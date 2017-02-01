<?php

namespace PokeCliBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PokeCliBundle\Services\PokeCliService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
        return $this->render(
            'PokeCliBundle:parts:search_form.html.twig',
            [
                'form' => $this->getSearchForm()->createView(),
            ]
        );
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
        /** @var PokeCliService $pokeService */
        $pokeCliService = $this->get('pokecli.service');
        /** @var Client $client */
        $client = $this->get('guzzle.client.pokepedia');
        /** @var Response $response */
        $response = $client->get('/api/pokemon/random');

        if (!$response->getStatusCode() === 200) {
            throw new \Exception($response->getReasonPhrase());
        }

        $randomPokemon = json_decode($response->getBody()->getContents(), true);

        return $this->render(
            'PokeCliBundle:full:pokemon_details.html.twig',
            [
                'pokemon' => $randomPokemon,
                'compareStats' => $pokeCliService->getCompareStats($randomPokemon),
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

            return $this->redirect(
                $this->generateUrl(
                    'search_results',
                    $params
                )
            );
        }

        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * Search Results page action
     *
     * @Route("/search/{pattern}", name="search_results", requirements={"pattern": "[a-z]+"})
     * @param string $pattern
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function searchAction($pattern = '*')
    {
        /** @var Client $client */
        $client = $this->get('guzzle.client.pokepedia');
        /** @var Response $response */
        $response = $client->get('/api/search/'.$pattern);

        if (!$response->getStatusCode() === 200) {
            throw new \Exception($response->getReasonPhrase());
        }

        $results = json_decode($response->getBody()->getContents(), true);

        return $this->render(
            'PokeCliBundle:full:search_results.html.twig',
            ['results' => $results]
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
        /** @var PokeCliService $pokeService */
        $pokeCliService = $this->get('pokecli.service');

        /** @var Client $client */
        $client = $this->get('guzzle.client.pokepedia');
        /** @var Response $response */
        $response = $client->get('/api/pokemon/details/'.$name);

        if (!$response->getStatusCode() === 200) {
            throw new \Exception($response->getReasonPhrase());
        }

        /** Pokemon $pokemon */
        $pokemon = json_decode($response->getBody()->getContents(), true);

        return $this->render(
            'PokeCliBundle:full:pokemon_details.html.twig',
            [
                'pokemon' => $pokemon,
                'compareStats' => $pokeCliService->getCompareStats($pokemon),
            ]
        );
    }
}
