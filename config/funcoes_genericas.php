<?php

/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function vazio($valor = '') {

  $valor = trim($valor);
  if ( is_numeric($valor) ) {
    return (int)$valor <= 0;
  }

  return empty(trim($valor));
}


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function data_valida($data) {

  if ( empty($data) ) {
    return false;
  }

	$data = explode('-', $data); // fatia a string $dat em pedados, usando / como referência
	$y = $data[0];
	$m = $data[1];
	$d = $data[2];
 
	$res = checkdate($m, $d, $y); // VERIFICA SE A DATA É VÁLIDA!

  if ( $res == 1 ) {
    return true;
	}
  return false;
	
}


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function data_formatada($date = '', $date_time = false) {

  if ( $date_time ) {
    return date('d/m/Y à\s H:i:s', strtotime($date));
  }

  return date('d/m/Y', strtotime($date));

}


/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function formata_cpf_cnpj($cnpj_cpf)
{
  if (strlen(preg_replace("/\D/", '', $cnpj_cpf)) === 11) {
    $response = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
  } else {
    $response = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
  }

  return $response;
}



/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function somente_numeros($string = '') {
  return preg_replace('/[^0-9]/', '', $string);
}



/**
 * Função
 * @author Antonio Ferreira <@toniferreirasantos>
 * @return 
*/
function cpf_cnpj_valido($cpf_cnpj) {

  // VERIFICA SE UM NÚMERO FOI INFORMADO
  if(empty( trim($cpf_cnpj) )) {
    return false;
  }



  // FORMATA O NUMERO
	// $cpf_cnpj = ereg_replace('[^0-9]', '', $cpf_cnpj);
	$cpf_cnpj = preg_replace('/[^0-9]/', '', $cpf_cnpj);
	$cpf_cnpj = str_pad($cpf_cnpj, 11, '0', STR_PAD_LEFT);

  // SE FOR CPF
  if (strlen($cpf_cnpj) == 11) {

    // Verifica se o numero de digitos informados é igual a 11 
    if (strlen($cpf_cnpj) != 11) {
      return false;
    }
    
    // Verifica se nenhuma das sequências invalidas abaixo 
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cpf_cnpj == '00000000000' || 
      $cpf_cnpj == '11111111111' || 
      $cpf_cnpj == '22222222222' || 
      $cpf_cnpj == '33333333333' || 
      $cpf_cnpj == '44444444444' || 
      $cpf_cnpj == '55555555555' || 
      $cpf_cnpj == '66666666666' || 
      $cpf_cnpj == '77777777777' || 
      $cpf_cnpj == '88888888888' || 
      $cpf_cnpj == '99999999999') {
      return false;
      // Calcula os digitos verificadores para verificar se o
      // CPF é válido
      } else {   
        
      for ($t = 9; $t < 11; $t++) {
          
        for ($d = 0, $c = 0; $c < $t; $c++) {
          $d += $cpf_cnpj{$c} * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf_cnpj{$c} != $d) {
          return false;
        }
      }
    
      return true;
    }
    
  } // IF CPF
  


  // SE FOR CNPJ
  if (strlen($cpf_cnpj) == 14)  {
    
    //Zera a Soma
    $soma = 0;
    
    $soma += ($cpf_cnpj[0] * 5);
    $soma += ($cpf_cnpj[1] * 4);
    $soma += ($cpf_cnpj[2] * 3);
    $soma += ($cpf_cnpj[3] * 2);
    $soma += ($cpf_cnpj[4] * 9); 
    $soma += ($cpf_cnpj[5] * 8);
    $soma += ($cpf_cnpj[6] * 7);
    $soma += ($cpf_cnpj[7] * 6);
    $soma += ($cpf_cnpj[8] * 5);
    $soma += ($cpf_cnpj[9] * 4);
    $soma += ($cpf_cnpj[10] * 3);
    $soma += ($cpf_cnpj[11] * 2); 
    
    $d1 = $soma % 11; 
    $d1 = $d1 < 2 ? 0 : 11 - $d1; 
    
    $soma = 0;
    $soma += ($cpf_cnpj[0] * 6); 
    $soma += ($cpf_cnpj[1] * 5);
    $soma += ($cpf_cnpj[2] * 4);
    $soma += ($cpf_cnpj[3] * 3);
    $soma += ($cpf_cnpj[4] * 2);
    $soma += ($cpf_cnpj[5] * 9);
    $soma += ($cpf_cnpj[6] * 8);
    $soma += ($cpf_cnpj[7] * 7);
    $soma += ($cpf_cnpj[8] * 6);
    $soma += ($cpf_cnpj[9] * 5);
    $soma += ($cpf_cnpj[10] * 4);
    $soma += ($cpf_cnpj[11] * 3);
    $soma += ($cpf_cnpj[12] * 2); 
    
    
    $d2 = $soma % 11; 
    $d2 = $d2 < 2 ? 0 : 11 - $d2; 
    
    if ($cpf_cnpj[12] == $d1 && $cpf_cnpj[13] == $d2){
      return true;
    }
    return false;
    
  } // IF CNPJ


  return false;
}  


