<?php

// str_replace(['-'], '_', $array[0])

$last_level_uri = last_level_uri();

$app->get("/negocios/{$last_level_uri}", ClienteController::class  ":{$last_level_uri}");
