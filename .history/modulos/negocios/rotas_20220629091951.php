<?php

$last_level_uri = last_level_uri();

if ( modo_dev() ) {
    $classe = 'ClienteController';
    $app->get("/negocios/{$last_level_uri}", $classe::class . ":{$last_level_uri}");
}



