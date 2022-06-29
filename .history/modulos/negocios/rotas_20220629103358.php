<?php

$last_level_uri = last_level_uri();

// $app->get('/negocios/teste2', ClienteController::class . ':teste2');


$modulos = [
    'negocios' => [
        ClienteController::class
    ]
];

$modulos = ['negocios'];

if ( modo_dev() ) {
 
    foreach ($classes as $classe) {
        $app->get("/{$classe->diretorio}/{$last_level_uri}", $classe->nome . ":{$last_level_uri}");
    }
}
