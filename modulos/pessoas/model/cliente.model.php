<?php

use Psr\Http\Message\ServerRequestInterface;

class ClienteModel extends PessoaModel
{

	



	

	/**
	 * Método cadastro()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function cadastro(ServerRequestInterface $request) {

		$post = (object)$request->getParsedBody();

		# VERIFICANDO PERMISSÕES DO MÓDULO CLIENTE
		( new UsuarioController() )->checa_permissao_acesso($post->id_usuario, 15);
		
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		# VALIDANDO CAMPOS

		if ( vazio($post->nome_razao_social) ) {
			return erro("Campo [NOME / RAZÃO SOCIAL] não informado!");
		}

		$post->nome_propriedade_fazenda = !vazio($post->nome_propriedade_fazenda) ? mb_strtoupper($post->nome_propriedade_fazenda, 'UTF-8') : null;

		if ( !in_array($post->id_situacao, [1, 2]) ) return erro("Campo [SITUAÇÃO] inválido!");
		if ( !in_array($post->id_tipo_pessoa, range(1, 5)) ) return erro("Campo [TIPO DE PESSOA] inválido!");

		if ( !vazio($post->CPF_CNPJ) ) {

			if ( !cpf_cnpj_valido($post->CPF_CNPJ) ) {
				return erro('Campo [CPF / CNPJ] inválido!');
			}
			
			$post->CPF_CNPJ = somente_numeros($post->CPF_CNPJ);
		}

		if ( !vazio($post->nascimento) && !data_valida($post->nascimento) ) {
			return erro('Campo [DATA DE NASCIMENTO] inválida!');
		}

		if ( isset($post->nascimento) && strtotime($post->nascimento) > strtotime(DATA_ATUAL) ) {
			return erro('Campo [DATA DE NASCIMENTO] inválida! (data futura)');
		}

		if ( !vazio($post->cep) && strlen($post->cep) < 8 ) {
			return erro("Campo [CEP] inválido!");
		}

		if ( strlen($post->telefone_fixo) > 0 && strlen($post->telefone_fixo) < 10) {
			return erro('Campo [TELEFONE] inválido!');
		}

		if ( !vazio($post->email_usuario) && !valida_email($post->email_usuario) ) {
			return erro("[E-MAIL] INVÁLIDO!");
		}

		$post->email_usuario = strtolower($post->email_usuario);

		if ( !vazio($post->telefone_celular) && !valida_celular($post->telefone_celular) ) {
			return erro("Número de [CELULAR] INVÁLIDO!");
		}

		$post->id_estado = vazio($post->id_estado) ? 11 	: $post->id_estado;
		$post->id_cidade = vazio($post->id_cidade) ? null : $post->id_cidade;

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		foreach ($post as $nome_campo => $valor) {
			if ( vazio($valor) ) {
				$post->$nome_campo = null;
			}
		}
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		if ( (int)$post->id_pessoa > 0 ) {
			return $this->update($post);
		}
		
		return $this->insert($post);
	}

	






	






	






	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	/**
	 * Método insert()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	private function insert($cliente) {

		$connect = $this->conn->conectar();

		if ( !vazio($cliente->email_usuario) ) {

			# VERIFICANDO DADOS REPETIDOS NO BANCO
			$query =
			"	SELECT * FROM tab_pessoas
				WHERE (
					lower(email_usuario) = :email_usuario
				)
			";
			$stmt = $connect->prepare($query);
			if(!$stmt) {
				return erro("Erro: {$connect->errno} - {$connect->error}", 500);
			}
		
			$stmt->bindParam(':email_usuario', $cliente->email_usuario);
			
			if( !$stmt->execute() ) {
				return erro("SQLSTATE[0]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
			}
			if ( $stmt->rowCount() > 0 ) {
				return erro("Já existe um cadastro com o e-mail '{$cliente->email_usuario}' Verifique e tente novamente.", 400, [$cliente]);
			}
			
		}
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$connect->beginTransaction();

		$query_insert =
		"	INSERT INTO tab_pessoas (
				id_plano_adesao,
    		id_situacao_assinatura,
				id_usuario_sistema,

				nome_razao_social,

				rg_ie,
				CPF_CNPJ,
				nascimento,
				email_usuario,
				telefone_fixo,
				telefone_celular,
				
				id_estado,
				id_cidade,
				
				cep,
				Numero,
				bairro,
				logradouro,
				complemento,

				data_limite_licenca,
    		informacoes_diversas,
				nome_propriedade_fazenda,

				id_situacao,
				id_tipo_pessoa,

				DATA_CRIACAO,
				DATA_ATUALIZACAO,
				ID_USUARIO_CRIACAO,
				ID_USUARIO_ATUALIZACAO
			)
			VALUES (

				'1',   -- GRATUITO PARA TESTAR [id_plano_adesao]
    		'106', -- EM EXPERIÊNCIA [id_situacao_assinatura]
				:id_usuario_sistema,

				upper(:nome_razao_social),
				
				:rg_ie,
				:CPF_CNPJ,
				:nascimento,
				:email_usuario,
				:telefone_fixo,
				:telefone_celular,

				:id_estado,
				:id_cidade,
				:cep,

				upper(:Numero),
				upper(:bairro),
				upper(:logradouro),
				upper(:complemento),

				-- [data_limite_licenca] -> 5 DIAS A PARTIR DA DATA DO CADASTRO',
				DATE_ADD(curdate(), INTERVAL 5 DAY),

				-- [informacoes_diversas]
				'Cadastrado realizado via API do App Mobile',
				:nome_propriedade_fazenda,

				:id_situacao,
				:id_tipo_pessoa,

				CURDATE(),
				CURDATE(),
				'1', -- [ID_USUARIO_CRIACAO] -> PROVISÓRIO
				'1'  -- [ID_USUARIO_ATUALIZACAO] -> PROVISÓRIO
			)
		";

		$stmt = $connect->prepare($query_insert);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		$stmt->bindParam(':nome_razao_social', $cliente->nome_razao_social);

		$stmt->bindParam(':rg_ie', $cliente->rg_ie);
		$stmt->bindParam(':CPF_CNPJ', $cliente->CPF_CNPJ);
		$stmt->bindParam(':nascimento', $cliente->nascimento);
		$stmt->bindParam(':email_usuario', $cliente->email_usuario);
		$stmt->bindParam(':telefone_fixo', $cliente->telefone_fixo);
		$stmt->bindParam(':telefone_celular', $cliente->telefone_celular);

		$stmt->bindParam(':id_estado', $cliente->id_estado, PDO::PARAM_INT);
		$stmt->bindParam(':id_cidade', $cliente->id_cidade, PDO::PARAM_INT);

		$stmt->bindParam(':id_situacao', $cliente->id_situacao, PDO::PARAM_INT);
		$stmt->bindParam(':id_tipo_pessoa', $cliente->id_tipo_pessoa, PDO::PARAM_INT);
		$stmt->bindParam(':id_usuario_sistema', $cliente->id_proprietario, PDO::PARAM_INT);
		$stmt->bindParam(':nome_propriedade_fazenda', $cliente->nome_propriedade_fazenda);

		$stmt->bindParam(':cep', $cliente->cep);
		$stmt->bindParam(':Numero', $cliente->Numero);
		$stmt->bindParam(':bairro', $cliente->bairro);
		$stmt->bindParam(':logradouro', $cliente->logradouro);
		$stmt->bindParam(':complemento', $cliente->complemento);
		


		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Cliente não cadastrado!");
		}

		$id_user_adicionado = $connect->lastInsertId();
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$connect->commit();
		return sucesso("CADASTRO REALIZADO COM SUCESSO!" . (modo_dev() ? " - [$id_user_adicionado]" : ''), [$cliente], 201);
	}























	/**
	 * Método update()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	private function update($cliente) {

		$connect = $this->conn->conectar();

		if ( !vazio($cliente->email_usuario) ) {

			# VERIFICANDO DADOS REPETIDOS NO BANCO
			$query =
			"	SELECT * FROM tab_pessoas
				WHERE (
					lower(email_usuario) = :email_usuario
					AND id_pessoa <> :id_usuario
				)
			";

			$stmt = $connect->prepare($query);
			if(!$stmt) {
				return erro("Erro: {$connect->errno} - {$connect->error}", 500);
			}
		
			$stmt->bindParam(':email_usuario', $cliente->email_usuario);
			$stmt->bindParam(':id_usuario', $cliente->id_pessoa, PDO::PARAM_INT);

			if( !$stmt->execute() ) {
				return erro("SQLSTATE[0]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
			}
			if ( $stmt->rowCount() > 0 ) {
				return erro("Já existe um cadastro com o e-mail '{$cliente->email_usuario}' Verifique e tente novamente.", 400, [$cliente]);
			}

		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$SUBQUERY_CAMPOS_NAO_OBRIGATORIOS = '';
		$campos_nao_obrigatorios = [
			'cep',
			'rg_ie',
			'Numero',
			'bairro',
			'CPF_CNPJ',
			'nascimento',
			'logradouro',
			'complemento',
			
			'telefone_fixo',

			'informacoes_diversas',
			'nome_propriedade_fazenda'
		];

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


		foreach ($cliente as $nome_campo => $valor) {

			if ( in_array($nome_campo, $campos_nao_obrigatorios) ) {
				$valor = str_replace(['"', "'", '´', '`'], '', $valor);
				$valor = vazio($valor) ? 'null' : "'{$valor}'";
				$SUBQUERY_CAMPOS_NAO_OBRIGATORIOS .= "\n{$nome_campo} = $valor,";
			}

		}


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		$connect->beginTransaction();

		$query_update =
		"	UPDATE tab_pessoas SET
				nome_razao_social = :nome_razao_social,
				
				telefone_celular = :telefone_celular,
				email_usuario = :email_usuario,

				id_estado = :id_estado,
				id_cidade = :id_cidade,
				id_situacao = :id_situacao,
				id_tipo_pessoa = :id_tipo_pessoa,

				{$SUBQUERY_CAMPOS_NAO_OBRIGATORIOS}

				DATA_ATUALIZACAO = CURDATE(),
				ID_USUARIO_ATUALIZACAO = :id_usuario
			WHERE (
				id_pessoa = :id_pessoa AND
				id_usuario_sistema = :id_proprietario
			)
		";

		$stmt = $connect->prepare($query_update);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		$stmt->bindParam(':id_pessoa', $cliente->id_pessoa, PDO::PARAM_INT);
		$stmt->bindParam(':id_usuario', $cliente->id_usuario, PDO::PARAM_INT);
		$stmt->bindParam(':id_proprietario', $cliente->id_proprietario, PDO::PARAM_INT);

		$stmt->bindParam(':nome_razao_social', $cliente->nome_razao_social);

		$stmt->bindParam(':telefone_celular', $cliente->telefone_celular);
		$stmt->bindParam(':email_usuario', $cliente->email_usuario);

		$stmt->bindParam(':id_estado', $cliente->id_estado, PDO::PARAM_INT);
		$stmt->bindParam(':id_cidade', $cliente->id_cidade, PDO::PARAM_INT);
		$stmt->bindParam(':id_situacao', $cliente->id_situacao, PDO::PARAM_INT);
		$stmt->bindParam(':id_tipo_pessoa', $cliente->id_tipo_pessoa, PDO::PARAM_INT);
		
		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		$connect->commit();
		return sucesso("CADASTRO ATUALIZADO COM SUCESSO!" . ($stmt->rowCount() <= 0 ? " - NENHUMA INFORMAÇÃO ALTERADA! " : ''), $cliente);
	}



	
	/**
	 * Método delete()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
	public function delete(ServerRequestInterface $request) {

		if ( REQUEST_METHOD != 'DELETE' ) {
			return erro("REQUEST_METHOD inválido!");
		}
		
		$post = (object)$request->getParsedBody();

		if ( !is_numeric($post->id_pessoa) || (int)$post->id_pessoa <= 0 ) {
			msg_debug("CAMPO [ID_PESSOA] INVÁLIDO!");
			return erro("Pessoa não identificada!", 400, $post);
		}
		
		if ( (int)$post->id_pessoa == (int)$post->id_proprietario ) {
			return erro('PESSOA INFORMADA NÃO PODE SER DELETADA! - USUÁRIO MASTER', 404, [$post]);
		}

		if ( (int)$post->id_pessoa == (int)$post->id_usuario ) {
			return erro('PESSOA INFORMADA NÃO PODE SER DELETADA! (AUTO DELETE)', 404, [$post]);
		}

		$connect = $this->conn->conectar();

		$query =
		" SELECT
				id_pessoa, id_usuario_sistema, nome_razao_social
			FROM tab_pessoas WHERE id_pessoa = :id_pessoa
		";
		$stmt = $connect->prepare($query);
		if( !$stmt ) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		$stmt->bindParam(':id_pessoa', $post->id_pessoa, PDO::PARAM_INT);
		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {   
			msg_debug("ID DE PESSOA {$post->id_pessoa} NÃO EXISTE NO BANCO!");
			return erro("PESSOA INFORMADA NÃO EXISTE NA BASE DE DADOS!", 404, [$post]);
		}
		
		$pessoa = $stmt->fetch(PDO::FETCH_OBJ);

		if ( (int)$pessoa->id_usuario_sistema != (int)$post->id_proprietario ) {
			msg_debug("ID DE PESSOA '{$post->id_pessoa}' PERTENCE A FAZENDA DE ID '{$pessoa->id_usuario_sistema}'!");
			return erro('PESSOA INFORMADA NÃO EXISTE EM SUA BASE DE DADOS!', 404, [$post]);
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$connect->beginTransaction();

		$query_delete =
		" DELETE FROM tab_pessoas
			WHERE (
				id_pessoa = :id_pessoa AND
				id_usuario_sistema = :id_proprietario
			)
		";
		$stmt = $connect->prepare($query_delete);
		if( !$stmt ) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		$stmt->bindParam(':id_pessoa', $post->id_pessoa, PDO::PARAM_INT);
		$stmt->bindParam(':id_proprietario', $post->id_proprietario, PDO::PARAM_INT);
		if( !$stmt->execute() ) {

			if ( $stmt->errorInfo()[1] == 1451 ) {
				msg_debug($stmt->errorInfo()[2]);
				return erro("ESTE REGISTRO CONTÉM UM OU MAIS REGISTROS RELACIONADOS!", 500);
			}

			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			msg_debug("REGISTRO DE PESSOA {$post->id_pessoa} NÃO EXCLUÍDO - MOTIVO DESCONHECIDO!");
			return erro("PESSOA NÃO EXCLUÍDA!");
		}
		$connect->commit();

		return sucesso('PESSOA EXCLUÍDA COM SUCESSO!');
	}


}
