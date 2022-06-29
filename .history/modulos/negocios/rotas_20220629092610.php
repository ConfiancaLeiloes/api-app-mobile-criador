<?php

$last_level_uri = last_level_uri();

$classes = [
    (object)[
        'diretorio' => 'negocios',
        'nome' => ClienteController::class
    ]
];



if ( modo_dev() ) {
 
    foreach ($classes as $classe) {
        $app->get("/{$classe->diretorio}/{$last_level_uri}", $classe->nome, . ":{$last_level_uri}");
    }
}
