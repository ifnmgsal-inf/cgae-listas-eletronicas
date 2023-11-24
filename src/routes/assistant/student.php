<?php

use \App\Http\Response;
use \App\Controller\Assistant\Students;
use \App\Controller\Assistant\Student;

// ADICIONANDO A ROTA DE CONSULTA DE ALUNOS
$router->get("/ass/alunos", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ()
    {
        return new Response(200, Students\Students::getView());
    }
]);

// ADICIONANDO A ROTA DE CADASTRO DE ALUNOS
$router->get("/ass/alunos/cadastrar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ()
    {
        return new Response(200, Student\NewStudent::getView());
    }
]);

// ADICIONANDO A ROTA DE CADASTRO DE ALUNOS (POST)
$router->post("/ass/alunos/cadastrar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request)
    {
        return new Response(200, Student\NewStudent::setView($request));
    }
]);

// ADICIONANDO A ROTA DE ATUALIZAÇÃO GERAL DE ALUNOS
$router->get("/ass/alunos/atualizar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ()
    {
        return new Response(200, Students\Update::getView());
    }
]);

// ADICIONANDO A ROTA DE ATUALIZAÇÃO GERAL DE ALUNOS (POST)
$router->post("/ass/alunos/atualizar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request)
    {
        return new Response(200, Students\Update::setView($request));
    }
]);

// ADICIONANDO A ROTA DE DESABILITAÇÃO GERAL DE ALUNOS
$router->get("/ass/alunos/desativar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ()
    {
        return new Response(200, Students\Disable::getView());
    }
]);

// ADICIONANDO A ROTA DE DESABILITAÇÃO GERAL DE ALUNOS (POST)
$router->post("/ass/alunos/desativar", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request)
    {
        return new Response(200, Students\Disable::setView($request));
    }
]);

// ADICIONANDO A ROTA DE ATUALIZAÇÃO DE ALUNO
$router->get("/ass/alunos/atualizar/{id}", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($id)
    {
        return new Response(200, Student\Update::getView($id));
    }
]);

// ADICIONANDO A ROTA DE ATUALIZAÇÃO DE ALUNO (POST)
$router->post("/ass/alunos/atualizar/{id}", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request, $id)
    {
        return new Response(200, Student\Update::setView($request, $id));
    }
]);

// ADICIONANDO A ROTA DE EXCLUSÃO DE ALUNO (POST)
$router->post("/ass/alunos/excluir/{id}", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request, $id)
    {
        return new Response(200, Student\Delete::setView($request, $id));
    }
]);

// ADICIONANDO A ROTA DE CONSULTA DE ALUNO
$router->get("/ass/alunos/{id}", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($id)
    {
        return new Response(200, Student\Student::getView($id));
    }
]);

// ADICIONANDO A ROTA DE CONSULTA DE ALUNO (POST)
$router->post("/ass/alunos/{id}", [
    "middlewares" => [
        "recover-cookies",
        "require-assistant-login",
        "update-lists"
    ],

    function ($request, $id)
    {
        return new Response(200, Student\Student::setView($request, $id));
    }
]);

?>