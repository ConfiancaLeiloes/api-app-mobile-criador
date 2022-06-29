<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
$app->get('/', function (Request $request, Response $response, $args) {
  $response->getBody()->write(json_encode(["Mensagem" => "API CONFIANCA APP"]));
  return $response;
});

$app->get('/negocios/teste3', ClienteController::class . ':index');
