<?php


	
/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function modo_dev() {
  return
    isset($_GET['debug']) ||
    isset($_GET['modo_dev']) ||
    isset($_POST['modo_dev']) ||
    $_SERVER['SERVER_NAME'] == 'localhost';
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
function uri_contem($substring) {
  if ( strpos(REQUEST_URI, $substring) === false ) {
    return false;
  }
  return true;
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
    
    'token_valido' => isset($_SESSION['token_valido']) ? $_SESSION['token_valido'] : true,
    'tem_permissao' => isset($_SESSION['tem_permissao']) ? $_SESSION['tem_permissao'] : true,
    
    'http_status_code' => $http_status_code,
    'modo_dev' => modo_dev(),
    'data_hora_requisicao' => DATA_HORA_ATUAL,
    
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

  if ( empty($objeto) ) {
    $objeto = $_POST;
  }

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



function cripto($string) {
  return base64_encode(base64_encode(base64_encode($string)));
}
function descripto($string) {
  return base64_decode(base64_decode(base64_decode($string)));
}



function valida_token($id_modulo = 0) {
  
  @header("Content-type: application/json; charset=utf-8");

  $post = body_params();
  $post->token;

  if ( !isset($post->token) ) {
    exit(erro("Token de acesso não encontrado!"));
  }
  if ( vazio($post->token) ) {
    exit(erro("Token de acesso não informado!"));
  }

  $inicio_sessao = descripto(explode('-', $post->token)[1]);

  if ( vazio($inicio_sessao) ) {
    exit(erro("Token de Sessão não encontrado!"));
  }
  
  $d0 = strtotime(date('Y-m-d 00:00:00'));
  $d1 = strtotime(date('Y-m-d 23:59:59')); # Determinando uma sessão de 24h
  $limite_sessao = $inicio_sessao + ($d1 - $d0);

  if ( strtotime(DATA_HORA_ATUAL) > $limite_sessao) {
    // exit(erro("Token de acesso expirado!"));
  }


  
  
  if ( $id_modulo > 0 ) {
    if ( $post->id_proprietario <= 0 ) {
      exit(erro("Usuário não identificado!"));
    }
    
    
  }
  
  
}















function dispara_email($MENSAGEM, $assunto, $email_destinatário, $email_remetente = 'contato@confiancaleiloes.digital') {
  
	# APLICANDO TRIM NOS PARÂMETROS
	$assunto 			      = trim($assunto);
	$MENSAGEM 				  = trim($MENSAGEM);
	$email_remetente    = trim($email_remetente);
	$email_destinatário = trim($email_destinatário);

  $email_remetente = !vazio($email_remetente) ? $email_remetente : EMAIL_CONFIANCA;

	# VERIFICA SE ALGUM PARAMETRO VEIO VAZIO;
	if ( 
    vazio($assunto)
    || vazio($MENSAGEM)
    
    || vazio($email_remetente)
    || vazio($email_destinatário)

    || !valida_email($email_remetente)
    || !valida_email($email_destinatário)
  ) {
		return false;
	}


	// CORPO DO E-MAIL
  $corpo_email = 
  "<html>
    <body style='background:#f2f2f2 !important; padding-top: 10px !important; padding-bottom: 10px !important; font-family:sans-serif !important;'>
      <div style='width:800px !important; margin:50px auto !important; background:#fff;'>
        <br>
        
        <div style='min-height: 220px !important; padding:20px !important; text-align:center !important;'>
          $MENSAGEM
        </div>

      </div>
    </body>
  </html>
  ";
  
  #  TurboSMTP
  require PATH_CDN  . '/php/services/TurboSMTP/TurboApiClient.php'; 

  $email = new Email();
  $email->setFrom($email_remetente); # E-mail de Origem

  $email->setToList($email_destinatário); # E-MAIL DO REMETENTE
  $email->setSubject($assunto); # Assunto do E-mail
  $email->setHtmlContent($corpo_email); // Conteúdo em HTML

  // Login no TurboSMTP
  $turboApiClient = new TurboApiClient("contato@agrobold.com.br", "Nb6zzDwM");
  $response = $turboApiClient->sendEmail($email); // Envia o E-mail e recebe o retorno do Servidor TurboSMTP

  return $response['message'] == 'OK';
}