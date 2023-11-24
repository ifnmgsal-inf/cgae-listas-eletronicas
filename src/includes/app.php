<?php

// ADICIONANDO OS ARQUIVOS DO COMPOSER
require __DIR__."/../../vendor/autoload.php";

use \App\Utils\View;
use \App\Utils\Environment;
use \App\Utils\MakePDF;
use \App\Utils\Database\Database;
use \App\Http\Middlewares\Queue;

// CARREGANDO AS VARIÁVIES DE AMBIENTE
Environment::load(__DIR__."/../../");

define("URL", getenv("URL"));

// CONFIGURANDO A CONEXÃO COM A BASE DE DADOS
Database::config(
    getenv("DB_HOST"),
    getenv("DB_NAME"),
    getenv("DB_USER"),
    getenv("DB_PASS"),
    getenv("DB_PORT")
);

// INICIALIZANDO AS VARIÁVEIS PADRÃO DE RENDENIZAÇÃO
View::init([
    "URL" => URL,
    "SERVER_URI" => getenv("SERVER_URI")
]);

// CONFIGURANDO AS CLASSES RESPECTIVAS AOS MIDDLEWARES
Queue::setMap([
    "maintenance" => \App\Http\Middlewares\Maintenance::class,
    "update-lists" => \App\Http\Middlewares\UpdateLists::class,
    "update-signatures" => \App\Http\Middlewares\UpdateSignatures::class,
    "recover-cookies" => \App\Http\Middlewares\RecoverCookies::class,
    "require-login" => \App\Http\Middlewares\RequireLogin::class,
    "without-login" => \App\Http\Middlewares\WithoutLogin::class,
    "require-student-login" => \App\Http\Middlewares\RequireStudentLogin::class,
    "require-assistant-login" => \App\Http\Middlewares\RequireAssistantLogin::class,
    "require-admin-login" => \App\Http\Middlewares\RequireAdminLogin::class
]); 

// CONFIGURANDO OS MIDDLEWARES PADRÕES DE TODAS AS ROTAS
Queue::setDefault([
    "maintenance"
]);

// VERIFICANDO OS ARQUIVOS DE CONFIGURAÇÃO DO MÓDULO DE EXPORTAÇÃO DE DADOS PARA PDF
MakePDF::checkVfsFile();