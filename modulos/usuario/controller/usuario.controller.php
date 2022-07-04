<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsuarioController 
{
	private $usuario;
	public function __construct($usuario)
	{
		$this->$usuario = new UsuarioModel();
	}


	













	
	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function login(ServerRequestInterface $request, ResponseInterface $response) {

	
		$body = (object)$request->getParsedBody();	
		
		if ( !isset($body->email) ) {
			return json("Campo [E-MAIL] não informado!", $response);
		}
		if ( !isset($body->senha) ) {
			return json("Campo [SENHA] não informado!", $response);
		}

		if ( vazio($body->email) ) {
			return json("Informe o [E-MAIL]!", $response);
		}
		if ( vazio($body->senha) ) {
			return json("Informe a [SENHA]!", $response);
		}

		if ( !valida_email($body->email) ) {
			return json("[E-MAIL] INVÁLIDO!", $response);
		}
		if ( strlen($body->senha) < 4 ) {
			return json("[SENHA] INVÁLIDA!", $response);
		}


		$body->plataforma = $body->plataforma == 'ios' ? 102 : 101;

		$res = $this->$usuario->login($body);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}







	
	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function recuperar_senha(ServerRequestInterface $request, ResponseInterface $response) {
	// public function recuperar_senha() {

		// $body = (object)$request->getParsedBody();	
		$body = (object)$_GET;
		
		if ( !isset($body->email) ) {
			return json("Campo [E-MAIL] não informado!", $response);
		}

		if ( vazio($body->email) ) {
			return json("Informe o [E-MAIL]!", $response);
		}

		if ( !valida_email($body->email) ) {
			return json("[E-MAIL] INVÁLIDO!", $response);
		}

		$res = $this->$usuario->recuperar_senha($body);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}







	/**
	 * Método token_valido() -> Verifica a validade do token da requisição corrente
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return boolean
	*/
	// public function valida_token(ServerRequestInterface $request, ResponseInterface $response) {
	public function valida_token() {
		
		$post = (object)$_POST;

		$msg_erro = '';

		if ( !isset($post->token) ) {
			$msg_erro = 'Token de acesso não encontrado!';
		}
		elseif ( vazio($post->token) ) {
			$msg_erro = 'Token de acesso não informado!';
		}
		elseif ( $post->id_usuario <= 0 ) {
      $msg_erro = 'Usuário não identificado!';
    }
		elseif ( $post->id_proprietario <= 0 ) {
      $msg_erro = 'Proprietário não identificado!';
    }
		else {
			
			$usuario = new UsuarioModel();
			if ( !$usuario->token_valido($post) ) {
				$msg_erro = 'Token inválido!';
			}

		}


		
		if ( !vazio($msg_erro) ) {
			$_SESSION['token_valido'] = false;
			@header("HTTP/1.1 400 ERRO NA REQUISIÇÃO!");
			@header("Content-type: application/json; charset=utf-8");			
			exit(erro($msg_erro));
		}

	}














	/**
	 * Método valida_token() -> Verifica a validade do token da requisição corrente
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return boolean
	*/
	public function checa_permissao_acesso($id_usuario = 0, $id_modulo = 0) {
		
		$msg_erro = '';
		if ( $id_modulo <= 0 ) {
      $msg_erro = 'Permissão não identificada!';
    }
		elseif ( $id_usuario <= 0 ) {
      $msg_erro = 'Usuário não identificado!';
    }
		elseif ( !$this->$usuario->tem_permissao_acesso($id_usuario, $id_modulo) ) {
			$msg_erro = 'Você não tem autorização para acessar este Conteúdo!';
		}
		
		if ( !vazio($msg_erro) ) {
			$_SESSION['tem_permissao'] = false;
			@header("HTTP/1.1 400 ERRO NA REQUISIÇÃO!");
			@header("Content-type: application/json; charset=utf-8");			
			exit(erro($msg_erro));
		}

	}








	/**
	 * Método perfil() -> Obtem os dados do perfil do usuário logado
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function perfil(ServerRequestInterface $request, ResponseInterface $response)
	{

		$post = (object)$request->getParsedBody();
		$this->checa_permissao_acesso($post->id_usuario, 1);

		if ( (int)$post->id_pessoa <= 0 ) {
			return json("USUÁRIO NÃO INFORMADO!", $response);
		}

		$res = $this->$usuario->perfil($post->id_pessoa);

		$res = json_decode($res);
		$usuario = $res->data[0];

		
		$usuario->CAMPOS_ADICIONAIS->PRIMEIRO_NOME_USUARIO = $usuario->PRIMEIRO_NOME_USUARIO;
		unset($usuario->PRIMEIRO_NOME_USUARIO);

		$usuario->CAMPOS_ADICIONAIS->NUM_ANIMAIS = $usuario->NUM_ANIMAIS;
		unset($usuario->NUM_ANIMAIS);

		if ( isset($usuario->nascimento) && data_valida($usuario->nascimento) ) {
			$usuario->CAMPOS_ADICIONAIS->DATA_NASCIMENTO_FORMAT = data_formatada($usuario->nascimento);
		}

		if ( isset($usuario->CPF_CNPJ) && !vazio(isset($usuario->CPF_CNPJ)) ) {
			$usuario->CPF_CNPJ = (string)$usuario->CPF_CNPJ;
			$usuario->CAMPOS_ADICIONAIS->CPF_CNPJ_FORMAT = formata_cpf_cnpj($usuario->CPF_CNPJ);
		}

		$usuario->cep = (string)$usuario->cep;
		$usuario->rg_ie = (string)$usuario->rg_ie;
		$usuario->Numero = (string)$usuario->Numero;

		$usuario->CAMPOS_ADICIONAIS->LOCALIZACAO = "{$usuario->nome_cidade}/{$usuario->sigla_estado}";

		$res->data[0] = $usuario;
		$res = json_encode($res);

		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}























	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/	
	public function cadastro(ServerRequestInterface $request, ResponseInterface $response) {

		// $post = body_params();
		$post = (object)$request->getParsedBody();

		if ( isset($post->id_pessoa) && ((int)$post->id_pessoa <= 0 || is_null($post->id_pessoa) || vazio($post->id_pessoa) ) ) {
			return json('Identificação de Usuário inválida!', $response);
		}

		# VALIDANDO {{NÃO}} CAMPOS OBRIGATÓRIOS
		if ( !vazio($post->CPF_CNPJ) ) {

			if ( !cpf_cnpj_valido($post->CPF_CNPJ) ) {
				return json('Campo [CPF / CNPJ] inválido!', $response);
			}
			
			$post->CPF_CNPJ = somente_numeros($post->CPF_CNPJ);
		}

		if ( isset($post->nascimento) && !vazio($post->nascimento) && !data_valida($post->nascimento) ) {
			return json('Campo [DATA DE NASCIMENTO] inválida!', $response);
		}

		if ( isset($post->cep) && !vazio($post->cep) ) {
			if ( strlen($post->cep) < 8 ) {
				return json("Campo [CEP] inválido!", $response);
			}
		}

		if ( isset($post->telefone_fixo) && strlen($post->telefone_fixo) < 8) {
			return json('Campo [TELEFONE] inválido!', $response);
		}


		
		# VALIDANDO CAMPOS OBRIGATÓRIOS
		/*
			nome_propriedade_fazenda,
			nome_razao_social
			telefone_celular
			email_usuario
			senha_usuario
			id_cidade
			id_estado
		*/ 


		if ( vazio($post->nome_razao_social) ) {
			return json("Campo [NOME / RAZÃO SOCIAL] não informado!", $response);
		}

		if ( vazio($post->nome_propriedade_fazenda) ) {
			return json("Campo [NOME NO HARAS / FAZENDA] não informado!", $response);
		}
		
		if ( vazio($post->email_usuario) ) {
			return json("Campo [E-MAIL] não informado!", $response);
		}

		if ( !valida_email($post->email_usuario) ) {
			return json("[E-MAIL] INVÁLIDO!", $response);
		}

		$post->email_usuario = strtolower($post->email_usuario);

		if ( vazio($post->senha_usuario) ) {
			return json("Campo [SENHA] não informado!", $response);
		}
		if ( strlen($post->senha_usuario) < 6 ) {
			return json("Campo [SENHA] inválido!", $response);
		}

		if ( vazio($post->telefone_celular) ) {
			return json("Campo [CELULAR] não informado!", $response);
		}



		if ( !valida_celular($post->telefone_celular) ) {
			return json("Número de [CELULAR] INVÁLIDO!", $response);
		}

		if ( (int)$post->id_cidade <= 0 ) {
			return json("Campo [CIDADE] não informado!", $response);
		}

		if ( (int)$post->id_estado <= 0 ) {
			return json("Campo [ESTADO / UF] não informado!", $response);
		}
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		foreach ($post as $nome_campo => $valor) {
			if ( vazio($valor) ) {
				$post->$nome_campo = null;
			}
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		if ( $post->id_pessoa > 0 ) {
			$this->checa_permissao_acesso($post->id_usuario, 1);
			$res = $this->$usuario->update($post);
		}
		else {
			$res = $this->$usuario->cadastro($post);
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		// return json(json_decode($res)->message, $response);
	
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');
	}



}
