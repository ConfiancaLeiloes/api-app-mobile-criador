<?php

// $app->get('/modulo/medodo', ClienteController::class . ':metodo');

$modulos = [
	'negocios' => [
		ClienteController::class
	]
];
	
	
$last_level_uri = last_level_uri();
foreach ($modulos as $nome_modulo => $classes) {
	foreach ($classes as $classe) {
		$app->$request_method("/{$nome_modulo}/{$last_level_uri}", $classe . ":{$last_level_uri}");
	}
}
