<?php

$REQUEST_URI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = strtolower($_SERVER['REQUEST_METHOD']);
$REQUEST_METHOD = strtoupper($_SERVER['REQUEST_METHOD']);
$DATA_ATUAL = date('Y-m-d');
$HORA_ATUAL = date('H:i:s');

define('REQUEST_URI', $REQUEST_URI);
define('REQUEST_METHOD', $REQUEST_METHOD);
define('request_method', $request_method);

define('DATA_ATUAL', $DATA_ATUAL);
define('HORA_ATUAL', $HORA_ATUAL);

define('DATA_HORA_ATUAL', $DATA_ATUAL .' '. $HORA_ATUAL);

define('EMAIL_DEV', 'toninhofulldev@gmail.com');
define('EMAIL_CONFIANCA', 'contato@confiancaleiloes.digital');

define('URL_FOTOS', "https://www.agrobold.com.br/agrobold_equinos/fotos_animais/");


define('PATH_CDN', '/home/wwgrup/cdn');

# Caminho onde as imagens dos animais serão cadastradas
define('PATH_UPLOAD_FOTOS', $_SERVER['DOCUMENT_ROOT'] . '/tests/imgs'); # Provisório -> Para testes

// $DB_MYSQL   = 'gc_confianca_criador';
// $HOST_MYSQL = 'confiancacriador.digital';
// $USER_MYSQL = 'gc_criador';
// $PASS_MYSQL = 'YDs-p(9Nr$%3';