<?php

namespace PokeRestBundle\Services;

use Buzz\Message\Response;
use Endroid\Twitter\Twitter;
use PokeRestBundle\Utils\Pokemon;
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
     * @param Twitter $twitter Twitter API Service
     * @param Client $redis Redis Service
     * @param array $pokeParams Pokepedia params
     */
    public function __construct(Twitter $twitter, Client $redis, $pokeParams)
    {
        $this->api = new PokeApi();
        $this->twitter = $twitter;
        $this->redis = $redis;
        $this->pokeParams = $pokeParams;
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
     * @param string $name Pokemon name
     * @param int $count number of tweets to return
     * @return array|string array of tweet IDs or false if count is too large
     */
    public function getPokemonTimeline($name, $count = 12)
    {
        $tweetIDs = [];

        if ($count > 50) {
            $count = 50;
        }

        // checks that Pokemon name exists
        if (!$this->redis->keys($name)) {
            return false;
        }

        // get tweets with the safe filter, disabled by default...
        $tweeterParams = [
            'q' => '#' . $name . ' filter:safe',
            'count' => $count
        ];

        /** @var Response $search */
        if ($search = $this->twitter->query('search/tweets', 'GET', 'json', $tweeterParams)) {
            $results = json_decode($search->getContent(), true);
            foreach ($results['statuses'] as $status) {
                $tweetIDs[] = $status['id_str'];
            }
        }
        return $tweetIDs;
    }

    /**
     * Returns Pokemon's stats then all types stats
     *
     * @param Pokemon $pokemon
     * @return array
     */
    public function getCompareStats(Pokemon $pokemon)
    {
        $fullStats[] = [
            'label' => ucfirst($pokemon->getName()),
            'borderColor' => $this->hexToRgbaColor('#0000FF', 0.6),
            'backgroundColor' => $this->hexToRgbaColor('#0000FF', 0.1),
            'pointHoverBackgroundColor' => $this->hexToRgbaColor('#0000FF', 0.6),
            'data' => array_values($pokemon->getStats())
        ];

        $this->redis->select($this->pokeParams['redis.databases']['types']);

        $typesColors = $this->pokeParams['types']['colors'];
        // limit types comparaison to pokemon's types
        $relatedTypes = $pokemon->getTypes();

        // get all types
        $typeNames = $this->redis->keys('*');
        foreach ($typeNames as $typeName) {
            $typeStats = $this->redis->hgetall($typeName);
            $typeStatsClean = [];
            foreach ($typeStats as $stateName => $statValue) {
                if ($stateName == 'total') {
                    continue;
                }
                $typeStatsClean[] = (int)$statValue;
            }

            $fullStats[] = [
                'label' => ucfirst($typeName),
                'hidden' => in_array($typeName, $relatedTypes) ? false : true,
                'borderColor' => $this->hexToRgbaColor($typesColors[$typeName], 0.4),
                'backgroundColor' => $this->hexToRgbaColor($typesColors[$typeName], 0.2),
                'pointHoverBackgroundColor' => $this->hexToRgbaColor($typesColors[$typeName], 0.6),
                'data' => $typeStatsClean
            ];
        }
        return $fullStats;
    }

    /**
     * Convert and Hex color to an rgba color
     *
     * @param string $hex Hex value of the color (eg. #FF0000)
     * @param float $rgbaOpacity Wanted rgba opacity
     * @return string rgba value
     */
    private function hexToRgbaColor($hex, $rgbaOpacity = 0.2)
    {
        $hex = str_replace('#', '', $hex);
        $hexParts = str_split($hex, 2);
        $rgba = [];
        foreach ($hexParts as $hexPart) {
            $rgba[] = hexdec($hexPart);
        }
        $rgba[] = $rgbaOpacity;
        return 'rgba(' . implode(',', $rgba) . ')';
    }

    /**
     * Returns a random Pokemon name
     *
     * @return Pokemon
     */
    public function getRandomPokemon()
    {
        $rndID = $this->redis->randomkey();
        return $this->getPokemonData(
            $this->getPokemonIdFromName($rndID)
        );
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
     * Returns Pokemon name for the related given name
     *
     * @param $name
     * @return string
     */
    public function getPokemonIdFromName($name)
    {
        return $this->redis->hget($name, 'id');
    }

    /**
     * Returns popularity (ie. likes and dislikes) of a given Pokemon
     *
     * @param $name Pokemon Name
     * @return array|bool Popularity array or false if not found
     */
    public function getPopularity($name)
    {
        if (!$this->redis->keys($name)) {
            return false;
        }
        return [
            'likes' => $this->redis->hget($name, 'likes'),
            'dislikes' => $this->redis->hget($name, 'dislikes')
        ];
    }

    /**
     * Returns Redis keys for the given pattern
     * ie. will returns all Pokemon names matching the pattern.
     * note: empty pattern returns all results
     *
     * @param $pattern
     * @return array
     */
    public function search($pattern)
    {
        $search = $this->redis->keys($pattern . '*');
        return [
            'pattern' => $pattern,
            'count' => count($search),
            'results' => $search
        ];
    }

    /**
     * Increment Like counter for the given pokemon then returns the current Like counter
     *
     * @param $name Pokemon Name
     * @return bool|int current like value for the Pokemon of false if
     */
    public function addLike($name)
    {
        if (!$this->redis->keys($name)) {
            return false;
        }
        return $this->redis->hincrby($name, 'likes', 1);
    }

    /**
     * Increment Dislike counter for the given pokemon then returns the current Like counter
     *
     * @param $name Pokemon Name
     * @return bool|int current dislike value for the Pokemon
     */
    public function addDislike($name)
    {
        if (!$this->redis->keys($name)) {
            return false;
        }
        return $this->redis->hincrby($name, 'dislikes', 1);
    }

    /**
     * Returns all type stats
     *
     * @return array of average base stats by Pokemon Type
     */
    public function getTypesStats()
    {
        $this->redis->select($this->pokeParams['redis.databases']['types']);
        $typeNames = $this->redis->keys('*');
        $typeStats = [];
        foreach ($typeNames as $typeName) {
            $typeStats[$typeName] = $this->redis->hgetall($typeName);
        }
        return $typeStats;
    }
}