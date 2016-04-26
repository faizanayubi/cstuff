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
        "pattern" => "success",
        "controller" => "home",
        "action" => "success"
    ),
    array(
        "pattern" => "home",
        "controller" => "home",
        "action" => "index"
    ),
    array(
        "pattern" => "plans",
        "controller" => "home",
        "action" => "plans"
    ),
    array(
        "pattern" => "contact",
        "controller" => "home",
        "action" => "contact"
    ),
    array(
        "pattern" => "products",
        "controller" => "home",
        "action" => "products"
    ),
    array(
        "pattern" => "termsofservice",
        "controller" => "home",
        "action" => "termsofservice"
    ),
    array(
        "pattern" => "login",
        "controller" => "auth",
        "action" => "login"
    ),
    array(
        "pattern" => "users/manage",
        "controller" => "client",
        "action" => "manage"
    )
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals
unset($routes);
