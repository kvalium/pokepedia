<?php

namespace PokeCliBundle\Controller;

use PokeRestBundle\Services\PokeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;


use Symfony\Component\HttpFoundation\Request;

class CliController extends Controller
{
    /** @var  Form $searchForm */
    private $searchForm;

    /**
     * @Route("/", name="home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('PokeCliBundle:full:index.html.twig');
    }

    public function renderSearchFormAction()
    {
        return $this->render('PokeCliBundle:parts:search_form.html.twig', [
            'form' => $this->getSearchForm()->createView()
        ]);
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
                ->add('name', SearchType::class, ['required' => false, 'trim' => true])
                ->add('send', SubmitType::class, ['label' => 'Search'])
                ->getForm();
            return $form;
        }
        return $this->searchForm;
    }

    /**
     * Will redirect to a randomly chosen Pokemon's page
     *
     * @Route("random", name="get_random_pokemon")
     */
    public function getRandomPokemonAction()
    {
        $pokeService = $this->get('poke.service');

        $randomPokemon = $pokeService->getRandomPokemon();

        return $this->render('PokeCliBundle:full:pokemon_details.html.twig',
            [
                'pokemon' => $randomPokemon,
                'compareStats' => $pokeService->getCompareStats($randomPokemon)
            ]
        );
    }

    /**
     * @Route("dosearch",name="search_handler")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function handleSearchAction(Request $request)
    {
        $form = $this->getSearchForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $params = [];
            if ($data['name']) {
                $params = ['pattern' => strtolower($data['name'])];
            }
            return $this->redirect($this->generateUrl(
                'search_results', $params
            ));
        }
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * Search Results page action
     *
     * @Route("/search/{pattern}", name="search_results", requirements={"pattern": "[a-z]+"})
     * @param $pattern
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction($pattern = '*')
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');

        return $this->render('PokeCliBundle:full:search_results.html.twig',
            ['results' => $pokeService->search($pattern)]
        );
    }

    /**
     * @Route("/details/{name}", name="pokemon_details")
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function detailsActions($name)
    {

        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        $pokemonID = $pokeService->getPokemonIdFromName($name);

        if (!$pokemonID) {
            throw new \Exception('unable to find a Pokemon');
        }

        /** Pokemon $pokemon */
        $pokemon = $pokeService->getPokemonData($pokemonID);


        return $this->render('PokeCliBundle:full:pokemon_details.html.twig',
            [
                'pokemon' => $pokemon,
                'compareStats' => $pokeService->getCompareStats($pokemon)
            ]
        );
    }
}
