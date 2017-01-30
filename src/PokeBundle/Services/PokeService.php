<?php

namespace PokeBundle\Services;

use Buzz\Message\Response;
use Endroid\Twitter\Twitter;
use PokeBundle\Utils\Pokemon;
use PokePHP\PokeApi;
use Predis\Client;

class PokeService
{
    /**
     * @var Twitter $twitter
     */
    private $twitter;
    /**
     * @var Client
     */
    private $redis;
    private $pokeParams;

    /**
     * Pokemon constructor.
     * @param Twitter $twitter
     * @param Client $redis
     * @param $pokeParams
     */
    public function __construct(Twitter $twitter, Client $redis, $pokeParams)
    {
        $this->api = new PokeApi();
        $this->twitter = $twitter;
        $this->redis = $redis;
        $this->pokeParams = $pokeParams;
    }

    /**
     * Return Pokemon related to the give ID
     *
     * @param int $pokemonID
     * @return Pokemon
     * @throws \Exception
     */
    public function getPokemonData($pokemonID)
    {
        $payload = json_decode($this->api->pokemon($pokemonID), true);
        if (!is_array($payload)) {
            throw new \Exception('PokeAPI returns the following error: ' . $payload);
        }
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

    /**
     * Returns latests tweets for a given Pokemon
     *
     * @param $name Pokemon name
     * @return array|string
     */
    public function getPokemonTimeline($name)
    {
        $tweetIDs = array();

        $tweeterParams = array(
            'q' => '#' . $name . ' filter:safe',
            'count' => 12
        );

        /** @var Response $search */
        if ($search = $this->twitter->query('search/tweets', 'GET', 'json', $tweeterParams)) {
            $results = json_decode($search->getContent(), true);
            foreach ($results['statuses'] as $status) {
                $tweetIDs[] = $status['id_str'];
            }
        }
        return $tweetIDs;
    }

    public function getCompareStats(Pokemon $pokemon)
    {
        $fullStats = array();
        $fullStats[] = array(
            'label' => ucfirst($pokemon->getName()),
            //'fill' => false,
            'borderColor' => $this->hexToRgbaColor('#0000FF', 0.6),
            'backgroundColor' => $this->hexToRgbaColor('#0000FF', 0.1),
            'pointHoverBackgroundColor' => $this->hexToRgbaColor('#0000FF', 0.6),
            'data' => array_values($pokemon->getStats())
        );

        $this->redis->select($this->pokeParams['redis.databases']['types']);

        $typesColors = $this->pokeParams['types']['colors'];
        // limit types comparaison to pokemon's types
        $relatedTypes = $pokemon->getTypes();

        // get all types
        $typeNames = $this->redis->keys('*');
        foreach ($typeNames as $typeName) {
            $typeStats = $this->redis->hgetall($typeName);
            $typeStatsClean = array();
            foreach ($typeStats as $stateName => $statValue) {
                if ($stateName == 'total') {
                    continue;
                }
                $typeStatsClean[] = (int)$statValue;
            }

            $fullStats[] = array(
                'label' => ucfirst($typeName),
                'hidden' => in_array($typeName, $relatedTypes) ? false : true,
                'borderColor' => $this->hexToRgbaColor($typesColors[$typeName], 0.4),
                'backgroundColor' => $this->hexToRgbaColor($typesColors[$typeName], 0.2),
                'pointHoverBackgroundColor' => $this->hexToRgbaColor($typesColors[$typeName], 0.6),
                // 'fill' => false,
                //'tension' => 0.1,
                'data' => $typeStatsClean
            );
        }
        return $fullStats;
    }

    public function getRandomPokemon()
    {
        // @TODO
    }

    private function hexToRgbaColor($hex, $rgbaOpacity = 0.2)
    {
        $hex = str_replace('#', '', $hex);
        $hexParts = str_split($hex, 2);
        $rgba = array();
        foreach ($hexParts as $hexPart) {
            $rgba[] = hexdec($hexPart);
        }
        $rgba[] = $rgbaOpacity;
        return 'rgba(' . implode(',', $rgba) . ')';
    }
}