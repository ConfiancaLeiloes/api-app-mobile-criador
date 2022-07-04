<?php

// $app->get('/modulo/medodo', ClienteController::class . ':metodo');

$modulos = [
	'negocios' => [
		ClienteController::class
	],
	'usuario' => [
		UsuarioController::class
	]
];
	
	
$nome_metodo = last_level_uri();
foreach ($modulos as $nome_modulo => $classes) {
	foreach ($classes as $classe) {
		$app->$request_method("/{$nome_modulo}/{$nome_metodo}", $classe . ":{$nome_metodo}");
	}
}

