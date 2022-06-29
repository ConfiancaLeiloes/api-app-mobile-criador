<?php

$last_level_uri = last_level_uri();

$classes = [ClienteController::class];

if ( modo_dev() ) {

    foreach ($classes as $classe) {
        $app->get("/negocios/{$last_level_uri}", $classe . ":{$last_level_uri}");
    }
}



