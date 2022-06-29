<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', function (Request $request, Response $response, $args) {
  $response->getBody()->write(json_encode(["Mensagem" => "API CONFIANCA APP"]));
  return $response;
});

// $app->post('/animais/detalhes-animal-cobricoes', AnimaisController::class . ':detalhes_animal_cobricoes');
// $app->post('/animais/detalhes-animal-exames', AnimaisController::class . ':detalhes_animal_exames');
// $app->post('/animais/detalhes-animal-filhos', AnimaisController::class . ':detalhes_animal_filhos');
// $app->post('/animais/detalhes-animal-genealogia', AnimaisController::class . ':detalhes_animal_genealogia');
// $app->post('/animais/detalhes-animal-manejo', AnimaisController::class . ':detalhes_animal_manejo');
// $app->post('/animais/detalhes-animal-negocios', AnimaisController::class . ':detalhes_animal_negocios');

$app->get('/negocios/teste3', ClienteController::class . ':index');
