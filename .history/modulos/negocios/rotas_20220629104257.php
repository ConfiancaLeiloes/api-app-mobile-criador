<?php


// $app->get('/modulo/medodo', ClienteController::class . ':metodo');

$last_level_uri = last_level_uri();
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
