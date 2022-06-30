<?php

use Psr\Http\Message\ServerRequestInterface;

class UsuarioModel
{

	private $conn;

	public function __construct($conn = null) {
		$this->conn = new ConexaoModel();
	}

	/**
	 * Método login()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function login() {

		// $connect = $this->conn->conectar();



		return erro("LOGIN Em desenvolvimento...");
	}



	/**
	 * Método perfil()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function perfil($id_pessoa) {

		$connect = $this->conn->conectar();

		$query = 
		"	SELECT
				tab_pessoas.id_pessoa,
				tab_pessoas.id_situacao,
				UPPER(tab_pessoas.nome_razao_social) AS nome_razao_social,
				UPPER(tab_pessoas.nome_propriedade_fazenda) AS nome_propriedade_fazenda,

				tab_pessoas.CPF_CNPJ,
				tab_pessoas.nascimento,
				tab_pessoas.rg_ie,
				tab_pessoas.email_usuario,
				tab_pessoas.telefone_fixo,
				tab_pessoas.telefone_celular,
				
				tab_pessoas.id_estado,
				tab_pessoas.id_cidade,

				tab_cidades.nome_cidade,
				tab_estados.sigla_estado,

				tab_pessoas.cep,
				tab_pessoas.logradouro,
				tab_pessoas.Numero,
				tab_pessoas.bairro,
				tab_pessoas.complemento,

				(
					SELECT count(id_animal) FROM tab_animais
					WHERE tab_animais.id_usuario_sistema = :id_pessoa
				) AS NUM_ANIMAIS


			FROM tab_pessoas
			LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
			JOIN tab_estados ON tab_estados.id_estado = tab_pessoas.id_estado 
			-- JOIN tab_sexos ON tab_sexos.id_sexo = tab_pessoas.id_sexo
			WHERE id_pessoa = :id_pessoa
		";
		
		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		$stmt->bindParam(":id_pessoa", $id_pessoa, PDO::PARAM_INT);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Usuário não encontrado na base de dados", 404);
		}

		$usuario = $stmt->fetchAll(PDO::FETCH_OBJ);
		return sucesso("Usuário encontrado! -> {$usuario[0]->nome_razao_social}", $usuario);
	}






	/**
	 * Método cadastro()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function cadastro($usuario) {

		// $connect = $this->conn->conectar();

		// $usuario->id_pessoa;
		// $usuario->id_situacao;
		// $usuario->nome_razao_social;
		// $usuario->nome_propriedade_fazenda;
		// $usuario->CPF_CNPJ;
		// $usuario->nascimento;
		// $usuario->rg_ie;
		// $usuario->email_usuario;
		// $usuario->telefone_fixo;
		// $usuario->telefone_celular;
		// $usuario->id_estado;
		// $usuario->id_cidade;
		// $usuario->nome_cidade;
		// $usuario->sigla_estado;
		// $usuario->cep;
		// $usuario->logradouro;
		// $usuario->Numero;
		// $usuario->bairro;
		// $usuario->complemento;

		return erro("CADASTRO em desenvolvimento...", 200, [$usuario]);
	}


	/**
	 * Método recuperar_senha()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function recuperar_senha() {
		return erro("REC SENHA Em desenvolvimento...");
	}

}
