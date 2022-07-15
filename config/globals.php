<?php

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$DATA_ATUAL = date('Y-m-d');
$HORA_ATUAL = date('H:i:s');
$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
$request_method = strtolower($_SERVER['REQUEST_METHOD']);
$REQUEST_METHOD = strtoupper($request_method);
$REQUEST_URI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

define('REMOTE_ADDR', $REMOTE_ADDR);
define('REQUEST_URI', $REQUEST_URI);
define('REQUEST_METHOD', $REQUEST_METHOD);
define('request_method', $request_method);
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

define('DATA_ATUAL', $DATA_ATUAL);
define('HORA_ATUAL', $HORA_ATUAL);
define('DATA_HORA_ATUAL', $DATA_ATUAL .' '. $HORA_ATUAL);

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

define('PATH_CDN', '/home/wwgrup/cdn'); # Caminho onde as imagens dos animais serão cadastradas
define('PATH_UPLOAD_FOTOS', $_SERVER['DOCUMENT_ROOT'] . '/tests/imgs'); # Provisório -> Para testes
define('URL_FOTOS', "https://www.agrobold.com.br/agrobold_equinos/fotos_animais/");

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# EMAILS
define('EMAIL_DEV', 'toninhofulldev@gmail.com');
define('EMAIL_CADASTRO', 'cadastro@confiancacriador.digital');      # Usado geralmente como Destinatário
define('EMAIL_COMERCIAL', 'comercial@confiancacriador.digital');    # Usado geralmente como Remetente
define('EMAIL_DIRETORIA', 'diretoria.ti@confiancaleiloes.digital'); # Usado geralmente como Destinatário

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~