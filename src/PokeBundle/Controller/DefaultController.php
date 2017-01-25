<?php

namespace PokeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PokePHP\PokeApi;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $api = new PokeApi;


        $redis = $this->get('snc_redis.default');

        // @todo move this to warmup command
//        $pokedex = json_decode($api->pokedex(1));
//        foreach($pokedex->pokemon_entries as $pokemon_entry){
//            $redis->set($pokemon_entry->pokemon_species->name,$pokemon_entry->pokemon_species->name);
//        };
        return $this->render('PokeBundle:Default:index.html.twig');
    }
    /**
     * @Route("/search/{pattern}")
     */
    public function searchAction($pattern){
        $redis = $this->get('snc_redis.default');
        dump($redis->keys($pattern.'*'));
        return $this->render('PokeBundle:Default:index.html.twig');
    }
}
