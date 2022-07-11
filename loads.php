<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL & ~(E_NOTICE|E_DEPRECATED));

require_once __DIR__.'/config/globals.php';

require_once __DIR__.'/config/funcoes.php';  
require_once __DIR__.'/config/funcoes_genericas.php';

require_once __DIR__.'/config/conexao.php'; 

require_once __DIR__.'/modulos/animais/model/animais.model.php';
require_once __DIR__.'/modulos/animais/controller/animais.controller.php'; 

require_once __DIR__.'/modulos/animais/model/reproducao.model.php';
require_once __DIR__.'/modulos/animais/controller/reproducao.controller.php';

require_once __DIR__.'/modulos/animais/model/sanitario.model.php';
require_once __DIR__.'/modulos/animais/controller/sanitario.controller.php';

require_once __DIR__.'/modulos/manejo/model/manejo.model.php';
require_once __DIR__.'/modulos/manejo/controller/manejo.controller.php';

require_once __DIR__.'/modulos/negocios/model/negocios.model.php';
require_once __DIR__.'/modulos/negocios/controller/negocios.controller.php';

require_once __DIR__.'/modulos/financeiro/model/financeiro.model.php';
require_once __DIR__.'/modulos/financeiro/controller/financeiro.controller.php';

require_once __DIR__.'/modulos/pessoas/model/pessoa.model.php';
require_once __DIR__.'/modulos/pessoas/controller/pessoa.controller.php';

require_once __DIR__.'/modulos/pessoas/model/usuario.model.php';
require_once __DIR__.'/modulos/pessoas/controller/usuario.controller.php';
