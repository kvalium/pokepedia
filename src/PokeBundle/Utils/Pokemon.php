<?php
/**
 * Created by PhpStorm.
 * User: kvalium
 * Date: 26/01/17
 * Time: 21:00
 */

namespace PokeBundle\Utils;


class Pokemon
{
    private $payload;
    private $baseExperience;
    private $weight;
    private $name;

    /**
     * Pokemon constructor.
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
        $this->name = $payload['name'];
        $this->weight = $payload['weight'];
        $this->baseExperience = $payload['base_experience'];
    }

    /**
     * @return mixed
     */
    public function getBaseExperience()
    {
        return $this->baseExperience;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Parse Types from payload
     *
     * @return array
     */
    public function getTypes()
    {
        $types = array();
        foreach ($this->payload['types'] as $type) {
            $types[] = $type['type']['name'];
        }
        return $types;
    }

    /**
     * Parse Stats from payload
     *
     * @return array
     */
    public function getStats()
    {
        $stats = array();
        foreach ($this->payload['stats'] as $stat) {
            $stats[$stat['stat']['name']] = $stat['base_stat'];
        }
        return $stats;
    }

    /**
     * Parse Sprites from payload
     *
     * @return array
     */
    public function getSprites()
    {
        $sprites = $this->payload['sprites'];
        return array(
            "default" => $sprites['front_default'],
            "female" => $sprites['front_female']
        );
    }

}