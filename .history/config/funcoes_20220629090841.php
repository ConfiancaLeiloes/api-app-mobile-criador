<?php

function modo_dev() {
  global $IP_ADRESS;
  return isset($_GET['debug']) || isset($_GET['modo_dev']) || $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
}



function get_last_path_rota() {

  $uri_array = explode('/', REQUEST_URI);

  $last_path_rota = '';
  foreach ($uri_array as $item) {
    if ( !empty(trim($item)) ) {
      $last_path_rota = $item;
    }
  }

  return $last_path_rota;
}
