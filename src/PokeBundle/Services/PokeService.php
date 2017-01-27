<?php

namespace PokeBundle\Services;

use PokeBundle\Utils\Pokemon;
use PokePHP\PokeApi;

class PokeService
{

    /**
     * Pokemon constructor.
     */
    public function __construct()
    {
        $this->api = new PokeApi();
    }

    /**
     * Return Pokemon related to the give ID
     *
     * @param int $pokemonID
     * @return Pokemon
     */
    public function getPokemonData($pokemonID)
    {
        $payload = json_decode($this->api->pokemon($pokemonID), true);
        /** @var Pokemon $pokemon */
        $pokemon = new Pokemon($payload);
        return $pokemon;
    }

    /**
     * Returns Pokedex payload related to the given ID
     *
     * @param $pokedexID
     * @return array Pokedex API payload
     */
    public function getPokedex($pokedexID)
    {
        return json_decode($this->api->pokedex($pokedexID));
    }
}