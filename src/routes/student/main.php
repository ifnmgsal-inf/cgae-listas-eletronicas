<?php

use \App\Http\Response;
use \App\Controller\Student;

// INCLUINDO AS ROTAS DE CADA MÓDULO
include __DIR__."/list.php";
include __DIR__."/signature.php";

// ADICIONANDO A ROTA HOME DO ALUNO
$router->get("", [
    "middlewares" => [
        "recover-cookies",
        "require-student-login",
        "update-signatures"
    ],

    function () {
        return new Response(200, Student\Home::getHome());
    }
]);

// ADICIONANDO A ROTA DE PERFIL
$router->get("/perfil", [
    "middlewares" => [
        "require-student-login",
        "update-signatures"
    ],

    function () {
        return new Response(200, Student\Profile::getProfile());
    }
]);

?>