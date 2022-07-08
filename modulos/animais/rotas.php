<?php

$modulos = [
	'animais' => [
		AnimaisController::class
	],
	'reproducao' => [
		ReproducaoController::class
	],
	'sanitario' => [
		SanitarioController::class
	]
];
	
try {
	$nome_rota = last_level_uri();
	
	$nome_metodo = str_replace('-', '_', last_level_uri());
	// $nome_metodo = str_replace('cadastro', 'cad', $nome_metodo);
	
	foreach ($modulos as $nome_modulo => $classes) {
		foreach ($classes as $classe) {
			$teste = $app->$request_method("/{$nome_modulo}/{$nome_rota}", $classe . ":{$nome_metodo}");
		}
	}
} catch (\Throwable $th) {
	throw new Exception($th->getMessage(), $th->getCode());
}	