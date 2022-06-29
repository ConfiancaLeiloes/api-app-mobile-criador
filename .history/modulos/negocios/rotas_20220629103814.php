<?php

$last_level_uri = last_level_uri();

// $app->get('/negocios/teste2', ClienteController::class . ':teste2');


$modulos = [
	'negocios' => [
		ClienteController::class
	]
];

foreach ($modulos as $nome_modulo => $classes) {
	foreach ($classes as $classe) {
		$app->get("/{$nome_modulo}/{$last_level_uri}", $classe . ":{$last_level_uri}");
	}
}
