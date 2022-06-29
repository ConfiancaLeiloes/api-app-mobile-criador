<?php

$last_level_uri = last_level_uri();

$classes = [
    (object)[
        'diretorio' => 'negocios',
        'nome' => ClienteController::class
    ]
];



if ( modo_dev() ) {
    # code...
    foreach ($classes as $classe) {
        $app->get("/{$classe->dir}/{$last_level_uri}", $classe->nome, . ":{$last_level_uri}");
    }
}
