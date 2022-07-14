<?php

if ( !isset($_GET['modo_dev']) ) {
  exit("Nops!");
}

$url_base = 'https://api.confiancacriador.digital';

foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/logs/requests/*.json') as $index => $json_file_path) {

  $json = file_get_contents($json_file_path);
  $obj = json_decode($json);
  
  if ( empty((array)$obj->recebido) ) {
    unlink($json_file_path);
    continue;
  }

  $json_file = end(explode('/', $json_file_path));
  
  echo str_pad($index, 2, '0', STR_PAD_LEFT);
  echo " - <a href='https://codebeautify.org/jsonviewer?url={$url_base}/logs/requests/{$json_file}' target='_blank'>{$json_file}</a><br>";
}
