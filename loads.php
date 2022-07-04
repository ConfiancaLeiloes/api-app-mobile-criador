<?php

require __DIR__ . '/vendor/autoload.php';

require_once __DIR__.'/config/globals.php';
require_once __DIR__.'/config/funcoes.php';  
require_once __DIR__.'/config/funcoes_genericas.php';

require_once __DIR__.'/config/conexao.php'; 

require_once __DIR__.'/modulos/animais/model/animais.model.php';
require_once __DIR__.'/modulos/animais/controller/animais.controller.php'; 

require_once __DIR__.'/modulos/animais/model/reproducao.model.php';
require_once __DIR__.'/modulos/animais/controller/reproducao.controller.php';


// require_once __DIR__.'/modulos/negocios/model/cliente.model.php';
// require_once __DIR__.'/modulos/negocios/controller/cliente.controller.php';

require_once __DIR__.'/modulos/usuario/model/usuario.model.php';
require_once __DIR__.'/modulos/usuario/controller/usuario.controller.php';
