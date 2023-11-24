<?php

use \App\Http\Response;
use \App\Controller\Assistant;

// INCLUINDO AS ROTAS DE CADA MÓDULO
include __DIR__."/signature.php";
include __DIR__."/student.php";

// ADICIONANDO A ROTA DO PAINEL DE ASSISTENTE
$router->get("/ass", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ()
    {
        return new Response(200, Assistant\Home::getView());
    }
]);

// ADICIONANDO A ROTA DE PERFIL
$router->get("/ass/perfil", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ()
    {
        return new Response(200, Assistant\Profile::getView());
    }
]);

// ADICIONANDO A ROTA DE EDIÇÃO DE PERFIL
$router->get("/ass/perfil/editar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request)
    {
        return new Response(200, Assistant\Profile::getViewEditProfile($request));
    }
]);

// ADICIONANDO A ROTA DE EDIÇÃO DE PERFIL (POST)
$router->post("/ass/perfil/editar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request)
    {
        return new Response(200, Assistant\Profile::setViewEditProfile($request));
    }
]);