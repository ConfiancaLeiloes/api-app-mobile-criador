<?php

$app->get('/negocios/detalhes-animal-cobricoes', ClienteController::class . ':detalhes_animal_cobricoes');
$app->get('/negocios/detalhes-animal-exames', ClienteController::class . ':detalhes_animal_exames');
$app->get('/negocios/detalhes-animal-filhos', ClienteController::class . ':detalhes_animal_filhos');
$app->get('/negocios/detalhes-animal-genealogia', ClienteController::class . ':detalhes_animal_genealogia');
$app->get('/negocios/detalhes-animal-manejo', ClienteController::class . ':detalhes_animal_manejo');
$app->get('/negocios/detalhes-animal-negocios', ClienteController::class . ':detalhes_animal_negocios');
