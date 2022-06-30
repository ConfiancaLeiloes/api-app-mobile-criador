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
		$res = $this->$usuario->login();
		$response->getBody()->write($res);

		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}











	/**
	 * Método perfil() -> Obtem os dados do perfil do usuário logado
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function perfil(ServerRequestInterface $request, ResponseInterface $response)
	{

		// return json("USUÁRIO NÃO INFORMADO!", 404);
		$res = erro("USUÁRIO NÃO INFORMADO!", 404);
		
		$post = body_params();
		if ( $post->id_pessoa > 0 ) {
			$res = $this->$usuario->perfil($post->id_pessoa);

			$res = json_decode($res);
			$usuario = $res->data[0];

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
		}

		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}












	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function recuperar_senha(ServerRequestInterface $request, ResponseInterface $response) {
		$res = $this->$usuario->recuperar_senha();
		$response->getBody()->write($res);

		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}














	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/	
	public function cadastro(ServerRequestInterface $request, ResponseInterface $response) {

		$post = body_params();

		if ( isset($post->id_pessoa) && ((int)$post->id_pessoa <= 0 || is_null($post->id_pessoa) || vazio($post->id_pessoa) ) ) {
			return json('Identificação de Usuário inválida!', $response);
		}
		


		# CAMPOS OBRIGATÓRIOS
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

		if ( isset($post->cep) && !vazio($post->cep) ) {
			if ( strlen($post->cep) < 8 ) {
				return json("Campo [CEP] inválido!", $response);
			}
		}

		if ( !vazio($post->CPF_CNPJ) ) {

			if ( !cpf_cnpj_valido($post->CPF_CNPJ) ) {
				return json('Campo [CPF / CNPJ] inválido!', $response);
			}
			
			$post->CPF_CNPJ = somente_numeros($post->CPF_CNPJ);
		}


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		if ( $post->id_pessoa > 0 ) {
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
