<?php


namespace PokeRestBundle\Controller;

use PokeRestBundle\Services\PokeService;
use PokeRestBundle\Utils\Pokemon;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RestController extends Controller
{

    /**
     * Returns Pokemon's Twitter latest related tweets
     *
     * @Rest\View()
     * @Rest\Get("/api/pokemon/tweets/{name}")
     * @param $name Pokemon Name
     * @return array|JsonResponse
     */
    public function getTimelineAction($name)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        if ($statuses = $pokeService->getPokemonTimeline($name)) {
            return [
                'pokemon' => $name,
                'tweets' => $statuses
            ];
        }
        return new JsonResponse(['message' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Returs a randomly chosen Pokemon
     *
     * @Rest\View()
     * @Rest\Get("/api/pokemon/random")
     * @return Pokemon
     */
    public function getRandomPokemon()
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        return $pokeService->getRandomPokemon();
    }

    /**
     * Returns Pokemon Popularity (likes / dislikes)
     *
     * @Rest\View()
     * @Rest\Get("/api/pokemon/popularity/{name}")
     * @param $name Pokemon Name
     * @return array|JsonResponse
     */
    public function getPopularityAction($name)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');

        if ($popularity = $pokeService->getPopularity($name)) {
            return [
                'pokemon' => $name,
                'popularity' => $popularity
            ];
        }
        return new JsonResponse(['message' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\View()
     * @Rest\Put("/api/pokemon/like/{name}")
     * @param $name Pokemon Name
     * @return array|JsonResponse
     */
    public function putLikeAction($name)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        if ($likes = $pokeService->addLike($name)) {
            return ['name' => $name, 'likes' => $likes];
        }
        return new JsonResponse(['message' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\View()
     * @Rest\Put("/api/pokemon/dislike/{name}")
     * @param $name Pokemon Name
     * @return array|JsonResponse
     */
    public function putDislikeAction($name)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        if ($dislikes = $pokeService->addDislike($name)) {
            return ['name' => $name, 'dislikes' => $dislikes];
        }
        return new JsonResponse(['message' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Returns Pokedex results for a given pattern
     *
     * @Rest\View()
     * @Rest\Get("/api/search/{pattern}")
     * @param string $pattern Search Pattern
     * @return array Search results
     */
    public function getSearchResults($pattern)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');
        return $pokeService->search($pattern);
    }

    /**
     * Returns Pokemon object
     *
     * @Rest\View()
     * @Rest\Get("/api/pokemon/details/{name}")
     * @param $name Pokemon Name
     * @return Pokemon|JsonResponse
     */
    public function getPokemonAction($name)
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');

        if ($pokemonID = $pokeService->getPokemonIdFromName($name)) {
            /** Pokemon $pokemon */
            return $pokeService->getPokemonData($pokemonID);
        }

        return new JsonResponse(['message' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Returns Types stats
     *
     * @Rest\View()
     * @Rest\Get("/api/types")
     * @return array|JsonResponse
     */
    public function getTypeStats()
    {
        /** @var PokeService $pokeService */
        $pokeService = $this->get('poke.service');

        return $pokeService->getTypesStats();
    }
}