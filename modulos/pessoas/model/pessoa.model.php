<?php

use Psr\Http\Message\ServerRequestInterface;

class PessoaModel
{

	protected $conn;

	public function __construct($conn = null) {
		$this->conn = new ConexaoModel();
	}


	
	/**
	 * Método perfil()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function perfil(ServerRequestInterface $request) {

		// $post = body_params();
		$pessoa = (object)$request->getParsedBody();

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
				) AS NUM_ANIMAIS,

				substring_index(upper	(tab_pessoas.nome_razao_social), ' ', 1) as PRIMEIRO_NOME_USUARIO


			FROM tab_pessoas
			LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
			JOIN tab_estados ON tab_estados.id_estado = tab_pessoas.id_estado 
			-- JOIN tab_sexos ON tab_sexos.id_sexo = tab_pessoas.id_sexo
			WHERE (
				id_pessoa = :id_pessoa AND 
				id_usuario_sistema = :id_proprietario
			)
		";
		
		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		$stmt->bindParam(':id_pessoa', $pessoa->id_pessoa, PDO::PARAM_INT);
		$stmt->bindParam(':id_proprietario', $pessoa->id_proprietario, PDO::PARAM_INT);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			msg_debug("REGISTRO NÃO ENCONTRADO NO BANCO DE DADOS! ID INFORMADO: [{$pessoa->id_pessoa}]");
			return erro("Usuário não encontrado na base de dados", 404);
		}

		$usuario = $stmt->fetch(PDO::FETCH_OBJ);
		$dados = [];
		
		$usuario->CAMPOS_ADICIONAIS = (object)[];
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
		$dados[] = $usuario;
		
		return sucesso("Usuário encontrado! -> {$usuario->nome_razao_social}", (array)["dados" => (array)$dados]);
	}
	
	














	/**
	 * Método proprietarios_criadores()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
	public function proprietarios_criadores(ServerRequestInterface $request) {

		$post = (object)$request->getParsedBody();

		if ( strlen(trim($post->id_pessoa)) > 0 ) {
			if ( !is_numeric(trim($post->id_pessoa)) || (int)trim($post->id_pessoa) < 0 ) return erro("Campo [id_pessoa] Inválido!");
		}

		$SUBQUERY_ID_PESSOA = (int)trim($post->id_pessoa) > 0 ? "AND tab_pessoas.id_pessoa = '{$post->id_pessoa}' " : '';

		$SUBQUERY_PALAVRA_CHAVE = '';
		if ( strlen(trim($post->palavra_chave)) > 2 ) {
			$SUBQUERY_PALAVRA_CHAVE =
			" AND (
					informacao_comercial_cliente LIKE '%{$post->palavra_chave}%'
					OR nome_propriedade_fazenda LIKE '%{$post->palavra_chave}%'
					OR informacoes_diversas LIKE '%{$post->palavra_chave}%'
					OR nome_razao_social LIKE '%{$post->palavra_chave}%' #
					OR email_usuario LIKE '%{$post->palavra_chave}%'
				)
			";
		}

		$connect = $this->conn->conectar();
		$query =
		"   SELECT
				id_pessoa, 
				upper(nome_razao_social) AS nome_razao_social
			FROM tab_pessoas
			WHERE (
				id_usuario_sistema = :id_proprietario 
				AND id_pessoa <> id_usuario_sistema
				{$SUBQUERY_PALAVRA_CHAVE}
				{$SUBQUERY_ID_PESSOA}
			)
		";


		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		$stmt->bindParam(':id_proprietario', $post->id_proprietario, PDO::PARAM_INT);
		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return sucesso("Nenhum registro encontrado!", [], 404);
		}



		return sucesso("{$stmt->rowCount()} REGISTROS ENCONTRADOS...", $stmt->fetchAll(PDO::FETCH_OBJ));
	}


}