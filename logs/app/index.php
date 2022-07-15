<link rel="stylesheet" href="css.css">
<?php

if ( !isset($_GET['modo_dev']) ) {
  exit("Nops!");
}

$url_base = 'https://api.confiancacriador.digital';
$array_geral = [];

foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/logs/requests/*.json') as $index => $json_file_path) {

  $json = file_get_contents($json_file_path);
  $obj = json_decode($json);
  
  if ( empty((array)$obj->recebido) ) {
    unlink($json_file_path);
    continue;
  }

  $json_file = end(explode('/', $json_file_path));
  $data = explode('_', $json_file)[0];

  if ( !isset($array_geral[$data]) ) {
    $array_geral[$data] = [];
  }

  array_push($array_geral[$data], $json_file);
}


$data_corrente = date('Y-m-d');
while ( true ) {

  if ( !isset($array_geral[$data_corrente]) ) {
    echo "<br><br><h3>FINISH!!</h3>";
    break;
  }

  echo "<h3>LOGS DE {$data_corrente} (".count($array_geral[$data_corrente]).")</h3>";
  
  rsort($array_geral[$data_corrente]);

  foreach ($array_geral[$data_corrente] as $nome_arquivo) {

    

    echo '<p>';
      
      echo str_pad(++$i, 3, '0', STR_PAD_LEFT), ' - ';
      echo substr($nome_arquivo, 11, 2), ':', substr($nome_arquivo, 13, 2), ':', substr($nome_arquivo, 15, 2), ':';

      echo " - <a href='https://codebeautify.org/jsonviewer?url={$url_base}/logs/requests/{$nome_arquivo}' target='_blank'>{$nome_arquivo}</a><br>";
    echo '</p>';
  }

  $data_corrente = date('Y-m-d', strtotime('-1 days', strtotime($data_corrente) ));
}

