<?php

use \App\Http\Response;
use \App\Controller\Student;

// ADICIONANDO A ROTA DA LISTA DE VAI E VOLTA
$router->get("/listas/vai_volta", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function () {
        return new Response(200, Student\Lists\VaiVolta::getVaiVolta());
    }
]);

// ADICIONANDO A ROTA DA LISTA DE VAI E VOLTA (POST)
$router->post("/listas/vai_volta", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function ($request) {
        return new Response(200, Student\Lists\VaiVolta::setVaiVolta($request));
    }
]);

// ADICIONANDO A ROTA DA LISTA DE SAÍDA 
$router->get("/listas/saida", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function () {
        return new Response(200, Student\Lists\Saida::getSaida());
    }
]);

// ADICIONANDO A ROTA DA LISTA DE SAÍDA (POST)
$router->post("/listas/saida", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function ($request) {
        return new Response(200, Student\Lists\Saida::setSaida($request));
    }
]);

// ADICIONANDO A ROTA DA LISTA DE PERNOITE (POST)
$router->get("/listas/pernoite", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function () {
        return new Response(200, Student\Lists\Pernoite::getPernoite());
    }
]);

$router->post("/listas/pernoite", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function ($request) {
        return new Response(200, Student\Lists\Pernoite::setPernoite($request));
    }
]);

?>