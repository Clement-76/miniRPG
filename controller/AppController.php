<?php

namespace ClementPatigny\Controller;

abstract class AppController {
    protected $twig;

    /**
     * AppController constructor.
     * Instantiates Twig_Loader_Filesystem and Twig_Environment classes
     * and stores $twig object into a property to reuse later in controllers
     */
    public function __construct() {
        $loader = new \Twig_Loader_Filesystem(ROOT . '/view');
        $twig = new \Twig_Environment($loader, [
            'cache' => false // ROOT . '/tmp'
        ]);

        $twig->addGlobal('baseUrl', baseUrl . '/');

        $this->twig = $twig;
    }
}
