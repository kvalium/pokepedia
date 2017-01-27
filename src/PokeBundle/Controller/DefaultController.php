<?php

namespace PokeBundle\Controller;

use PokeBundle\Services\PokeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /** @var  Form $searchForm */
    private $searchForm;

    /**
     * @Route("/", name="home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
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
        return $this->render('PokeBundle:full:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Returns the search form instance or create it
     * @return Form
     */
    private function getSearchForm()
    {
        if (!isset($this->searchForm)) {
            $defaultData = array('message' => 'Type your message here');
            /** @var Form $form */
            $form = $this->createFormBuilder($defaultData)
                ->add('name', TextType::class)
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
        $redis = $this->get('snc_redis.default');
        return $this->render('PokeBundle:full:search_results.html.twig',
            array(
                'searchResults' => $redis->keys($pattern . '*'),
            )
        );
    }

    /**
     * @Route("/details/{name}", name="pokemon_details")
     */
    public function detailsActions($name)
    {
        // @todo use service to get Pokemon data
        $pokemonData = array(
            'name' => $name
        );
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');

        /** Pokemon $pokemon */
        $pokemon = $pokeService->getPokemonData(5);

        return $this->render('PokeBundle:full:pokemon_details.html.twig',
            array(
                'weight' => $pokemon->getWeight(),
                'name' => $pokemon->getName(),
                'sprites' => $pokemon->getSprites()
            )
        );
    }
}
