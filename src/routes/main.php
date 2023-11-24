<?php

use \App\Controller\Common\Login;
use \App\Http\Response;

// INCLUINDO AS ROTAS GERAIS DO SITE
include __DIR__."/student/main.php";
include __DIR__."/assistant/main.php";

// ADICIONANDO A ROTA DE LOGIN
$router->get("/entrar", [
    "middlewares" => [
        "without-login"
    ],

    function ()
    {
        return new Response(200, Login::getView());
    }
]);

// ADICIONANDO A ROTA DE LOGIN (POST)
$router->post("/entrar", [
    "middlewares" => [
        "without-login"
    ],

    function ($request)
    {
        return new Response(200, Login::setView($request));
    }
]);

// ADCIONANDO A ROTA DE LOGOUT
$router->get("/sair", [
    "middlewares" => [
        "require-login"
    ],

    function ($request)
    {
        return new Response(200, Login::setLogout($request));
    }
]);