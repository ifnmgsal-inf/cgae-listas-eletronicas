<?php

// INCLUINDO O ARQUIVO DE CONFIGURAÇÕES GERAIS

require __DIR__."/src/includes/app.php";

use \App\Http\Router;

$router = new Router(URL);

// ADICIONANDO AS ROTAS DO SITE
include __DIR__."/src/routes/main.php";

// EXECUTANDO AS ROTAS ATUAIS
$router->run()->sendResponse();

?>