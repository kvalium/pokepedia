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
    private $ID;
    private $height;

    /**
     * Pokemon constructor.
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
        $this->ID = $payload['id'];
        $this->name = $payload['name'];
        $this->weight = $payload['weight'];
        $this->height = $payload['height'];
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
     * Returns Pokemon Weight in kilograms
     *
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight / 10;
    }

    /**
     * Returns Pokemon Height in meters
     *
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height / 10;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
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
        $pokeSprites = array();
        $pokeSprites['default'] = array(
            "front" => $sprites['front_default'],
            "back" => $sprites['back_default'],
        );

        // append female sprites if defined
        if(isset($sprites['front_female'])){
            $pokeSprites['female'] = array(
                "front" => $sprites['front_female'],
                "back" => $sprites['back_female'],
            );
        }

        return $pokeSprites;
    }

}