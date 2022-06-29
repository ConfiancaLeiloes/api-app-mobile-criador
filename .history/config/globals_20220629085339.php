<?php

$REQUEST_URI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
define(REQUEST_URI, $REQUEST_URI);

// $DB_MYSQL   = 'gc_confianca_criador';
// $HOST_MYSQL = 'confiancacriador.digital';
// $USER_MYSQL = 'gc_criador';
// $PASS_MYSQL = 'YDs-p(9Nr$%3';