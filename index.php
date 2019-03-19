<?php

define('ROOT', __DIR__);
date_default_timezone_set('Europe/Paris');

require 'composer/vendor/autoload.php';
session_start();

$action = 'login';
$controller = '\ClementPatigny\Controller\\'; // the namespace

if (isset($_GET['action']) && !empty($_GET['action']) && preg_match('#.+\..+#', $_GET['action'])) {
    // the format of the GET parameter is controllerName.action
    $data = explode('.', $_GET['action']);
    $action = $data[1];
    $controller .= ucfirst($data[0]) . 'Controller';
} else {
    $controller .= 'UsersController';
}

if (class_exists($controller) && method_exists($controller, $action)) {
    $controller = new $controller();
    $controller->$action();
} else {
    header("HTTP/1.0 404 Not Found");
    exit();
}
