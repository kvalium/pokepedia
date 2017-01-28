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
    private $redisDb;

    /**
     * Pokemon constructor.
     * @param Twitter $twitter
     * @param Client $redis
     * @param $redisDb
     */
    public function __construct(Twitter $twitter, Client $redis, $redisDb)
    {
        $this->api = new PokeApi();
        $this->twitter = $twitter;
        $this->redis = $redis;
        $this->redisDb = $redisDb;
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

    /**
     * Returns latests tweets for a given Pokemon
     *
     * @param $name Pokemon name
     * @return array|string
     */
    public function getPokemonTimeline($name)
    {
        $tweetIDs = array();

        /** @var Response $search */
        if($search = $this->twitter->query('search/tweets', 'GET', 'json', array('q' => '#'.$name))){
            $results = json_decode($search->getContent(), true);
            foreach($results['statuses'] as $status){
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
            'data' => array_values($pokemon->getStats())
        );

        $this->redis->select($this->redisDb['types']);

        // limit types comparaison to pokemon's types
        $relatedTypes = $pokemon->getTypes();
//        foreach($pokemon->getTypes() as $pokemonTypes){
//            $typeNames[] = $this->redis->keys($pokemonTypes)[0];
//        }
        // get all types
        $typeNames = $this->redis->keys('*');
        foreach($typeNames as $typeName){
            $typeStats = $this->redis->hgetall($typeName);
            $typeStatsClean = array();
            foreach($typeStats as $stateName => $statValue){
                if($stateName == 'total'){ continue; }
                $typeStatsClean[] = (int)$statValue;
            }

            $fullStats[] = array(
                'label' => ucfirst($typeName),
                'hidden' => in_array($typeName, $relatedTypes) ? false : true,
                //'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'data' => $typeStatsClean
            );
        }
        return $fullStats;
    }
}