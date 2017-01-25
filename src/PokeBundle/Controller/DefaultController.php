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
        return $this->render('PokeBundle:Default:index.html.twig');
    }
    /**
     * @Route("/search/{pattern}")
     */
    public function searchAction($pattern){
        $redis = $this->get('snc_redis.default');
        var_dump($redis->keys($pattern.'*'));
        return $this->render('PokeBundle:Default:index.html.twig');
    }
}
