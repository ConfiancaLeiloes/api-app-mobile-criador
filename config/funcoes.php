<?php


	
/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function modo_dev() {
  global $IP_ADRESS;
  return isset($_GET['debug']) || isset($_GET['modo_dev']) || $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
}

/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function type_request($type = '') { 
  return REQUEST_METHOD === strtoupper($type) ? true : false;
}


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function last_level_uri() {

  $uri_array = explode('/', REQUEST_URI);

  $last_path_rota = '';
  foreach ($uri_array as $item) {
    if ( !empty(trim($item)) ) {
      $last_path_rota = $item;
    }
  }

  return $last_path_rota;
}

/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function retorno($resultado, $mensagem, $http_status_code = 200, $dados = []) { 

  // if ( count($dados) > 0 ) {
  //   for ($i=0; $i < count($dados); $i++) { 
  //     $dados[$i] = numerics_json($dados[$i]);
  //   }
  // }

  return json_encode([
    'codigo' => (boolean)$resultado,
    'message' => $mensagem,
    
    'http_status_code' => $http_status_code,
    'modo_dev' => modo_dev(),
    'data_hora_requisicao' => date('Y-m-d H:i:s'),
    
    'data' => $dados
  ]);

}

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function sucesso($mensagem, $dados = [], $http_status_code = 200) { 
  return retorno(true, $mensagem, $http_status_code, $dados);
}

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function erro($mensagem, $http_status_code = 400, $dados = []) {
  return retorno(false, (modo_dev() ? "[MODO_DEV_ERROR_MSG]: $mensagem" : $mensagem), $http_status_code, $dados);
}

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function body_params() {
  
  $objeto = @json_decode(file_get_contents('php://input'), true);

  foreach ($objeto as $nome_campo => $valor) {
    $objeto[$nome_campo] = trim($valor);
  }

  return (object)$objeto;
}



/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function numerics_json($objeto, $ignore_list = []) {

  $campos = [];
  foreach ($objeto as $nome_campo => $valor) {
    
    $campos[$nome_campo] = $valor;

    if ( count($ignore_list) > 0 ) {
      foreach ($ignore_list as $nome_campo_ignore) {
        if ( $nome_campo_ignore == $nome_campo ) {
          continue 2;
        }
      }
    }

    if ( is_numeric($valor) ) {
      $campos[$nome_campo] = (float)$valor;
      continue;
    }
  }
  return (object)$campos;
}



/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function json($msg = '', $response, $http_status_code = 400) {
  
  $res = erro( vazio($msg) ? json_decode($res)->message : $msg );

  $response->getBody()->write($res);
  $http_status_code = $http_status_code != json_decode($res)->http_status_code ? $http_status_code : json_decode($res)->http_status_code;

  return $response->withStatus( $http_status_code )->withHeader('Content-type', 'application/json');	 
}