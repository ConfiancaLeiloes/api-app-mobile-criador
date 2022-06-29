<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(["Mensagem" => "API CONFIANCA APP"]));
    return $response;
});
$app->post('/negocios/detalhes-animal-cobricoes', ClienteController::class . ':detalhes_animal_cobricoes');
$app->post('/negocios/detalhes-animal-exames', ClienteController::class . ':detalhes_animal_exames');
$app->post('/negocios/detalhes-animal-filhos', ClienteController::class . ':detalhes_animal_filhos');
$app->post('/negocios/detalhes-animal-genealogia', ClienteController::class . ':detalhes_animal_genealogia');
$app->post('/negocios/detalhes-animal-manejo', ClienteController::class . ':detalhes_animal_manejo');
$app->post('/negocios/detalhes-animal-negocios', ClienteController::class . ':detalhes_animal_negocios');
