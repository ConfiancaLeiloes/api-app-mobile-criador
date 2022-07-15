<?php

$modulos =	[
	'pessoas' => PessoaController::class,
	'usuario' => UsuarioController::class,
	'clientes' => ClienteController::class,

  'manejo'  => ManejoController::class,
  'animais' => AnimaisController::class,

	'reproducao' => ReproducaoController::class,
	'sanitario'  => SanitarioController::class,
  'negocios'   => NegociosController::class,
	'financeiro' => FinanceiroController::class
];

$nome_rota = last_level_uri();
$nome_metodo = str_replace('-', '_', $nome_rota);

# $app->get('/modulo/metodo', ClassController::class . ':metodo');
try {

	foreach ($modulos as $nome_modulo => $classe) {
		$teste = $app->$request_method("/{$nome_modulo}/{$nome_rota}", $classe . ":{$nome_metodo}");
	}

}
catch (\Throwable $th) {
	throw new Exception($th->getMessage(), $th->getCode());
}	
