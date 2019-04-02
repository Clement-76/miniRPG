<?php

define('ROOT', __DIR__);
date_default_timezone_set('Europe/Paris');

require 'composer/vendor/autoload.php';
session_start();

$actualUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if (empty($_GET['url'])) {
    $url = '/';
    $baseUrl = rtrim($actualUrl, '/');
} else {
    $url = $_GET['url'];
    $baseUrl = rtrim(preg_replace("#/$url.*$#", '', $actualUrl), '/'); // the url without the GET parameters
}

define('baseUrl', $baseUrl);

$router = new ClementPatigny\Router\Router($url);

$router->addRoute('GET', '/', function() {
    $usersController = new ClementPatigny\Controller\UsersController();
    $usersController->login();
});

$router->addRoute('GET|POST', '/login/', function() {
    $usersController = new ClementPatigny\Controller\UsersController();
    $usersController->login();
});

$router->addRoute('GET', '/home/', function() {
    $homeController = new ClementPatigny\Controller\HomeController();
    $homeController->displayHome();
});

$router->addRoute('GET|POST', '/:controller/:action', function($controller, $action) {
    $controller = '\ClementPatigny\Controller\\' . ucfirst($controller) . 'Controller';

    if (class_exists($controller) && method_exists($controller, $action)) {
        $controller = new $controller();
        $controller->$action();
    } else {
        header("HTTP/1.0 404 Not Found");
        exit();
    }
});

$router->run();
