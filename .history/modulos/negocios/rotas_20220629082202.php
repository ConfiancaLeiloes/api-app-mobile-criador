<?php

$app->get('/negocios/detalhes-animal-cobricoes', AnimaisController::class . ':detalhes_animal_cobricoes');
$app->get('/negocios/detalhes-animal-exames', AnimaisController::class . ':detalhes_animal_exames');
$app->get('/negocios/detalhes-animal-filhos', AnimaisController::class . ':detalhes_animal_filhos');
$app->get('/negocios/detalhes-animal-genealogia', AnimaisController::class . ':detalhes_animal_genealogia');
$app->get('/negocios/detalhes-animal-manejo', AnimaisController::class . ':detalhes_animal_manejo');
$app->get('/negocios/detalhes-animal-negocios', AnimaisController::class . ':detalhes_animal_negocios');
