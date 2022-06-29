<?php

$last_level_uri = last_level_uri();

if ( modo_dev() ) {
    $classes = ClienteController::class;
    $app->get("/negocios/{$last_level_uri}", $classe . ":{$last_level_uri}");
}



