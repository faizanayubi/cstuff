<?php

// define routes

$routes = array(
    array(
        "pattern" => "features",
        "controller" => "home",
        "action" => "features"
    ),
    array(
        "pattern" => "cart/:id",
        "controller" => "home",
        "action" => "cart"
    ),
    array(
        "pattern" => "index",
        "controller" => "home",
        "action" => "index"
    ),
    array(
        "pattern" => "home",
        "controller" => "home",
        "action" => "index"
    ),
    array(
        "pattern" => "login",
        "controller" => "auth",
        "action" => "login"
    )
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals
unset($routes);
