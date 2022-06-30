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


// $DB_MYSQL   = 'gc_confianca_criador';
// $HOST_MYSQL = 'confiancacriador.digital';
// $USER_MYSQL = 'gc_criador';
// $PASS_MYSQL = 'YDs-p(9Nr$%3';