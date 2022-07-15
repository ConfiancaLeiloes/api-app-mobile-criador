<?php


	
/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function modo_dev() {

  // return false;

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
 * Função msg_debug
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return void
*/
function msg_debug($msg = '') { 
  
  if ( !empty($msg) ) {
    $_SESSION['debug'] = $msg;
  }
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

  $retorno = [
    'codigo' => (boolean)$resultado,
    'message' => $mensagem,
    'debug' => $_SESSION['debug'],
    
    'token_valido' => isset($_SESSION['token_valido']) ? $_SESSION['token_valido'] : true,
    'tem_permissao' => isset($_SESSION['tem_permissao']) ? $_SESSION['tem_permissao'] : true,
    
    'http_status_code' => $http_status_code,
    'data_hora_requisicao' => DATA_HORA_ATUAL
  ];


  if ( !isset($_SESSION['debug']) ) {
    unset($retorno['debug']);
  }
  
  if ( modo_dev() ) {
    $retorno['modo_dev'] = true;
  }

  $_SESSION['retorno'] = $retorno; # SEM O DATA

  $retorno['data'] = $dados;


  return json_encode($retorno);

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


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function cripto($string) {
  return base64_encode(base64_encode(base64_encode($string)));
}

/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function descripto($string) {
  return base64_decode(base64_decode(base64_decode($string)));
}


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
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














/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function dispara_email($MENSAGEM, $assunto, $email_destinatario, $email_remetente = '') {
  
	# APLICANDO TRIM NOS PARÂMETROS
	$assunto 			      = trim($assunto);
	$MENSAGEM 				  = trim($MENSAGEM);
	$email_remetente    = trim($email_remetente);
	$email_destinatario = trim($email_destinatario);

  $email_remetente = !vazio($email_remetente) ? $email_remetente : EMAIL_COMERCIAL;

	# VERIFICA SE ALGUM PARAMETRO VEIO VAZIO;
	if ( 
    vazio($assunto)
    || vazio($MENSAGEM)
    
    || vazio($email_remetente)
    || vazio($email_destinatario)

    || !valida_email($email_remetente)
    || !valida_email($email_destinatario)
  ) {
		return false;
	}

	# CORPO DO E-MAIL
  $corpo_email =
  " <html>
      <body style='width: 800px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important; font-weight: normal;  color: #545454'>
              
        <div style='margin-top: 30px;'>
          <img src='https://confiancacriador.digital/assets/img/logo.png' height='80' />
        </div>

        {$MENSAGEM}

        <div style='width:100%;margin-top: 30px;'>
          <p>Nossa equipe está à sua inteira disposição caso necessite de ajuda!</p>
          <h4 style='margin-bottom: 0 !important;'>Suporte:</h4>
          <p style='margin-top: 0 !important; margin-bottom: 0 !important;'>
            WhatsApp: <b>(31) 2118-1776 </b>– E-mail: <b>suporte@confiancacriador.digital</b>
          </p>
          <p style='margin-top: 0 !important;'>
            Site: <a href='https://confiancacriador.digital' target='_blank'></a>www.confiancacriador.digital
          </p>
        </div>

      </body>
    </html>
  ";
  
  #  TurboSMTP
  require PATH_CDN  . '/php/services/TurboSMTP/TurboApiClient.php'; 

  $email = new Email();
  $email->setFrom($email_remetente); # E-mail de Origem (REMETENTE)
  $email->setToList(modo_dev() ? EMAIL_DEV : $email_destinatario);
  $email->setSubject($assunto); # Assunto do E-mail
  $email->setHtmlContent($corpo_email); // Conteúdo em HTML

  // Login no TurboSMTP
  $turboApiClient = new TurboApiClient("contato@agrobold.com.br", "Nb6zzDwM");
  $response = $turboApiClient->sendEmail($email); // Envia o E-mail e recebe o retorno do Servidor TurboSMTP

  return $response['message'] == 'OK';
}