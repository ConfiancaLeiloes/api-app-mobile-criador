<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsuarioController extends PessoaController
{
	private $usuario;
	public function __construct()
	{
		$this->usuario = new UsuarioModel();
	}


	













	
	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function login(ServerRequestInterface $request, ResponseInterface $response) {

		$res = $this->usuario->login($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}







	
	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function recuperar_senha(ServerRequestInterface $request, ResponseInterface $response) {
		$res = $this->usuario->recuperar_senha( (object)$_GET );
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}







	/**
	 * Método token_valido() -> Verifica a validade do token da requisição corrente
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return boolean
	*/
	// public function valida_token(ServerRequestInterface $request, ResponseInterface $response) {
	public static function valida_token() {
		
		// $post = (object)$_POST;
		$post = body_params();

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
				$msg_erro = 'TOKEN INVÁLIDO!';
			}

		}

		
		if ( !vazio($msg_erro) ) {
			
			msg_debug($post);
			$_SESSION['token_valido'] = false;

			@header("HTTP/1.1 400 ERRO NA REQUISIÇÃO!");
			@header("Content-type: application/json; charset=utf-8");			
			exit(erro($msg_erro));
		}

	}














	/**
	 * Método checa_permissao_acesso() -> Verifica se o usuário logado tem acesso a uma determinada funcionalidade 
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
		elseif ( !$this->usuario->tem_permissao_acesso($id_usuario, $id_modulo) ) {
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


		$res = $this->usuario->perfil($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}


















	/**
	 * Método cadastro
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/	
	public function cadastro(ServerRequestInterface $request, ResponseInterface $response) {

		$post = (object)$request->getParsedBody();
			
		if ( (int)$post->id_pessoa > 0 ) {
			$this->valida_token();
			$this->checa_permissao_acesso($post->id_usuario, 1);
		}

		$res = $this->usuario->cadastro($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');
	}


}
