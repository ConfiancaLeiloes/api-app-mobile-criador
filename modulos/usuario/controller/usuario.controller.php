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

		if ( $post->id_pessoa > 0 ) {
		}
		
		# CAMPOS OBRIGATÓRIOS
		// $post->nome_razao_social;
		$post->nome_propriedade_fazenda;
		$post->telefone_celular;
		$post->email_usuario;
		$post->senha_usuario;
		$post->id_estado;
		$post->id_cidade;
		
		# CAMPOS {{NÃO}} OBRIGATÓRIOS
		$post->nascimento;
		$post->CPF_CNPJ; # NÃO OBRIGATÓRIO ???
		$post->rg_ie;
		$post->telefone_fixo;
		$post->cep;
		$post->logradouro;
		$post->Numero;
		$post->bairro;
		$post->complemento;

		
		if ( !vazio($post->CPF_CNPJ) ) {

			if ( !cpf_cnpj_valido($post->CPF_CNPJ) ) {
				// exit("DEBUG #1");
				$res = erro("Campo [CPF / CNPJ] inválido!");
			}
			else {
				$post->CPF_CNPJ = somente_numeros($post->CPF_CNPJ);
				$res = $this->$usuario->cadastro($post);
			}

		}
		else {

			if ( vazio($post->nome_razao_social) ) {
				$res = erro("Campo [NOME / RAZÃO SOCIAL] não informado!");
			}
			elseif ( vazio($post->nome_propriedade_fazenda) ) {
				$res = erro("Campo [NOME NO HARAS / FAZENDA] não informado!");
			}
			elseif ( vazio($post->email_usuario) ) {
				$res = erro("Campo [E-MAIL] não informado!");
			}
			elseif ( vazio($post->senha_usuario) ) {
				$res = erro("Campo [SENHA] não informado!");
			}
			elseif ( vazio($post->telefone_celular) ) {
				$res = erro("Campo [CELULAR] não informado!");
			}
			elseif ( (int)$post->id_cidade <= 0 ) {
				$res = erro("Campo [CIDADE] não informado!");
			}
			elseif ( (int)$post->id_estado <= 0 ) {
				$res = erro("Campo [ESTADO / UF] não informado!");
			}
			else {
				$res = $this->$usuario->cadastro($post);
			}
			
		}

		
		
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}



}
