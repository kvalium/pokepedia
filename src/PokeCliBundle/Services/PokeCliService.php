<?php

namespace PokeCliBundle\Services;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class PokeCliService
{

    private $pokeParams;
    /**
     * @var Client $pokeRest
     */
    private $pokeRest;

    /**
     * Pokemon constructor.
     * @param Client $pokeRest
     * @param array $pokeParams Pokepedia params
     */
    public function __construct(Client $pokeRest, $pokeParams)
    {
        $this->pokeParams = $pokeParams;
        $this->pokeRest = $pokeRest;
    }

    /**
     * Returns Pokemon's stats then all types stats
     *
     * @param array $pokemon
     * @return array
     * @throws \Exception
     */
    public function getCompareStats($pokemon)
    {
        $fullStats[] = [
            'label' => ucfirst($pokemon['name']),
            'borderColor' => $this->hexToRgbaColor('#0000FF', 0.6),
            'backgroundColor' => $this->hexToRgbaColor('#0000FF', 0.1),
            'pointHoverBackgroundColor' => $this->hexToRgbaColor('#0000FF', 0.6),
            'data' => array_values($pokemon['stats']),
        ];

        $typesColors = $this->pokeParams['types']['colors'];
        // limit types comparaison to pokemon's types
        $relatedTypes = $pokemon['types'];

        /** @var Response $response */
        $response = $this->pokeRest->get('/api/types');
        if (!$response->getStatusCode() === 200) {
            throw new \Exception($response->getReasonPhrase());
        }

        $typeNames = json_decode($response->getBody()->getContents(), true);

        // get all types
        foreach ($typeNames as $typeName => $typeStats) {
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
                'data' => $typeStatsClean,
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

        return 'rgba('.implode(',', $rgba).')';
    }
}