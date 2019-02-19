<?php

require 'composer/vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('view/');

$twig = new Twig_Environment($loader, [
    'cache' => false // __DIR__ . '/tmp' -> tmp -> temporary
]);

echo $twig->render('login_register.twig', [
    'name' => '<b>test</b>'
]);
