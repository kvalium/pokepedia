<?php

namespace PokeBundle\Controller;

use PokeBundle\Services\PokeService;
use Predis\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /** @var  Form $searchForm */
    private $searchForm;

    /**
     * @Route("/", name="home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('PokeBundle:full:index.html.twig');
    }

    public function renderSearchFormAction()
    {
        return $this->render('PokeBundle:parts:search_form.html.twig', array(
            'form' => $this->getSearchForm()->createView()
        ));
    }

    /**
     * @Route("search",name="search_handler")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function handleSearchAction(Request $request)
    {
        $form = $this->getSearchForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['name']) {
                return $this->redirect($this->generateUrl(
                    'search_results',
                    array('pattern' => strtolower($data['name']))
                ));
            }
        }
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * Returns the search form instance or create it
     * @return Form
     */
    private function getSearchForm()
    {
        if (!isset($this->searchForm)) {
            /** @var Form $form */
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('search_handler'))
                ->add('name', SearchType::class, array('required' => 'true', 'trim' => true))
                ->add('send', SubmitType::class, array('label' => 'Search'))
                ->getForm();
            return $form;
        }
        return $this->searchForm;
    }

    /**
     * Search Results page action
     *
     * @Route("/search/{pattern}", name="search_results", requirements={"pattern": "[a-z]+"})
     * @param $pattern
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction($pattern)
    {
        /** @var Client $redis */
        $redis = $this->get('snc_redis.default');
        return $this->render('PokeBundle:full:search_results.html.twig',
            array(
                'searchResults' => $redis->keys($pattern . '*'),
                'pattern' => $pattern
            )
        );
    }

    /**
     * @Route("/details/{name}", name="pokemon_details")
     */
    public function detailsActions($name)
    {
        /** @var Client $redis */
        $redis = $this->get('snc_redis.default');
        $pokemonID = $redis->hget($name, 'id');

        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');

        /** Pokemon $pokemon */
        $pokemon = $pokeService->getPokemonData($pokemonID);


        return $this->render('PokeBundle:full:pokemon_details.html.twig',
            array(
                'pokemon' => $pokemon,
                'compareStats' => $pokeService->getCompareStats($pokemon)
            )
        );
    }

    /**
     * @Route("/tweets/{name}", name="pokemon_twitter_timeline")
     */
    public function twitterTimelineAction(Request $request, $name)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        $statuses = $pokeService->getPokemonTimeline($name);
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($statuses);
        }
    }
}
