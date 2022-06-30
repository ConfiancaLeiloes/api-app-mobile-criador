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
	public function login($login) {

		$connect = $this->conn->conectar();

		$query = 
		"	SELECT  

				tab_pessoas.id_pessoa,
				tab_pessoas.id_situacao,
				tab_proprietario.data_limite_licenca,
				MD5(CONCAT(CURTIME(),tab_pessoas.id_pessoa)) as TOKEN
				
			FROM tab_fazendas_usuario 
			JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_fazendas_usuario.id_usuario
			JOIN tab_pessoas as tab_proprietario ON (
				tab_proprietario.id_pessoa = tab_fazendas_usuario.id_fazenda 
			)
			WHERE (
				tab_pessoas.email_usuario = :email AND 
				tab_pessoas.senha_usuario = :senha
			)
			GROUP BY tab_pessoas.id_pessoa
		";

		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		$stmt->bindParam(':email', $login->email);
		$stmt->bindParam(':senha', $login->senha);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("E-mail e/ou Senha incorretos! Verifique e tente novamente", 404);
		}
		if ( $stmt->rowCount() > 1 ) {
			return erro("Dados cadastrais duplicados! Contate a Confiança!");
		}

		$usuario = $stmt->fetch(PDO::FETCH_OBJ);
		if ( $usuario->id_situacao != 1 ) {
			return erro("Situação cadastral INATIVA! Contate a Confiança para maiores informações!");
		}

		if ( strtotime(DATA_ATUAL) > strtotime($usuario->data_limite_licenca) ) {
			return erro("Data limite de Licença expirada! Contate a Confiança para maiores informações!");
		}

		unset($usuario->id_situacao);
		unset($usuario->data_limite_licenca);

		return sucesso("Login Realizado com Sucesso!", [$usuario]);
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

				substring_index(tab_pessoas.nome_razao_social, ' ', 1) as PRIMEIRO_NOME_USUARIO,

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

		$connect = $this->conn->conectar();



		# VERIFICANDO DADOS REPETIDOS NO BANCO
		$query =
		"	SELECT * FROM tab_pessoas
			WHERE (
				lower(email_usuario) = :email_usuario
				AND id_pessoa = id_usuario_sistema
			)
		";

		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
	
		$stmt->bindParam(':email_usuario', $usuario->email_usuario);
		
		if( !$stmt->execute() ) {
			return erro("SQLSTATE[0]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() > 0 ) {
			return erro("Já existe um cadastro com o e-mail '{$usuario->email_usuario}' Verifique e tente novamente.", 400, [$usuario]);
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$connect->beginTransaction();


		# INSERT NOVO USUÁRIO
		$query_insert =
		"	INSERT INTO tab_pessoas (
				id_plano_adesao,
    		id_situacao_assinatura,

				nome_razao_social,
				nome_propriedade_fazenda,

				rg_ie,
				CPF_CNPJ,
				nascimento,
				email_usuario,
				senha_usuario,
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

				id_situacao,
				DATA_CRIACAO,
				DATA_ATUALIZACAO,
				ID_USUARIO_CRIACAO,
				ID_USUARIO_ATUALIZACAO
			)
			VALUES (

				'1',   -- GRATUITO PARA TESTAR [id_plano_adesao]
    		'106', -- EM EXPERIÊNCIA [id_situacao_assinatura]

				:nome_razao_social,
				:nome_propriedade_fazenda,
				
				:rg_ie,
				:CPF_CNPJ,
				:nascimento,
				:email_usuario,
				:senha_usuario,
				:telefone_fixo,
				:telefone_celular,

				:id_estado,
				:id_cidade,

				:cep,
				:Numero,
				:bairro,
				:logradouro,
				:complemento,

				-- [data_limite_licenca] -> 15 DIAS A PARTIR DA DATA DO CADASTRO',
				DATE_ADD(curdate(), INTERVAL 15 DAY),

				-- [informacoes_diversas]
				'Usuário cadastrado via API do App Mobile',

				'1', -- [id_situacao] -> PROVISÓRIO
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

		$stmt->bindParam(':nome_razao_social', $usuario->nome_razao_social);
		$stmt->bindParam(':nome_propriedade_fazenda', $usuario->nome_propriedade_fazenda);

		$stmt->bindParam(':rg_ie', $usuario->rg_ie);
		$stmt->bindParam(':CPF_CNPJ', $usuario->CPF_CNPJ);
		$stmt->bindParam(':nascimento', $usuario->nascimento);
		$stmt->bindParam(':email_usuario', $usuario->email_usuario);
		$stmt->bindParam(':senha_usuario', $usuario->senha_usuario);
		$stmt->bindParam(':telefone_fixo', $usuario->telefone_fixo);
		$stmt->bindParam(':telefone_celular', $usuario->telefone_celular);

		$stmt->bindParam(':id_estado', $usuario->id_estado, PDO::PARAM_INT);
		$stmt->bindParam(':id_cidade', $usuario->id_cidade, PDO::PARAM_INT);

		$stmt->bindParam(':cep', $usuario->cep);
		$stmt->bindParam(':Numero', $usuario->Numero);
		$stmt->bindParam(':bairro', $usuario->bairro);
		$stmt->bindParam(':logradouro', $usuario->logradouro);
		$stmt->bindParam(':complemento', $usuario->complemento);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Usuário não cadastrado!");
		}

		$id_user_adicionado = $connect->lastInsertId();


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		# UPDATE CADASTRO
		$query_update =
		" UPDATE tab_pessoas SET
				id_usuario_sistema = :id_user_adicionado,
				ID_USUARIO_CRIACAO = :id_user_adicionado,
				ID_USUARIO_ATUALIZACAO = :id_user_adicionado
			WHERE id_pessoa = :id_user_adicionado
		";

		$stmt = $connect->prepare($query_update);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		$stmt->bindParam(':id_user_adicionado', $id_user_adicionado, PDO::PARAM_INT);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE[1.1]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Usuário do sistema não atualizado!");
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


		# INSERT FAZENDA
		$query_insert_fazenda =
		"  INSERT INTO tab_fazendas_usuario (
				id_usuario,
				id_fazenda,
				informacoes_diversas,
				
				DATA_CRIACAO,
				DATA_ATUALIZACAO,

				ID_USUARIO_CRIACAO,
				ID_USUARIO_ATUALIZACAO
			) 
			VALUES (
				:id_usuario,
				:id_fazenda,
				'Cadastro via API do App Mobile',
				
				CURDATE(),
				CURDATE(),
				
				:ID_USUARIO_CRIACAO,
				:ID_USUARIO_ATUALIZACAO
			)
		";
		$stmt = $connect->prepare($query_insert_fazenda);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		$stmt->bindParam(':id_usuario', $id_user_adicionado, PDO::PARAM_INT);
		$stmt->bindParam(':id_fazenda', $id_user_adicionado, PDO::PARAM_INT);
		$stmt->bindParam(':ID_USUARIO_CRIACAO', $id_user_adicionado, PDO::PARAM_INT);
		$stmt->bindParam(':ID_USUARIO_ATUALIZACAO', $id_user_adicionado, PDO::PARAM_INT);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE[2]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Fazenda não cadastrada!");
		}

		$id_fazenda_adicionada = $connect->lastInsertId();


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		
		# OBTEBNDO PRIVILEGIOS
		$query_modulos_app = "SELECT group_concat(id_modulo_app) AS MODULOS FROM tab_modulos_aplicativo";
		$stmt = $connect->prepare($query_modulos_app);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		if( !$stmt->execute() ) {
			retorno_usuario('error', 'Erro: SQLSTATE: '. $stmt->errorInfo()[1]); // http://us3.php.net/pdo.errorInfo	
		}
		$modulos = $stmt->fetch(PDO::FETCH_OBJ);
  

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		# INSERT PRIVILEGIOS
		$query_insert_privilegios =
		"	INSERT INTO tab_privilegios_usarios_aplicativo (
				id_modulo,
				id_usuario_fazenda
			) 
			VALUES (
				:id_modulo,
				:id_usuario_fazenda
			)
		";
		$stmt = $connect->prepare($query_insert_privilegios);
		if( !$stmt ) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		foreach (explode(',', $modulos->MODULOS) as $id_modulo) {

			$stmt->bindParam(":id_modulo", $id_modulo, PDO::PARAM_INT);
			$stmt->bindParam(":id_usuario_fazenda", $id_fazenda_adicionada, PDO::PARAM_INT);
			
			if( !$stmt->execute() ) {
				return erro("SQLSTATE[3][{$id_modulo}]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
			}
			if ( $stmt->rowCount() <= 0 ) {
				return erro('Privilégio não cadastrado!');
			}
		
		} # foreach
		
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		# E-MAILS (??)
		
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		if ( !modo_dev() ) {
			$connect->commit();
		}
		return sucesso("CADASTRO REALIZADO COM SUCESSO!" . (modo_dev() ? " - [$id_user_adicionado]" : ''), [$usuario], 201);
	}























	/**
	 * Método update()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function update($usuario) {

		
		$connect = $this->conn->conectar();

		# VERIFICANDO DADOS REPETIDOS NO BANCO
		$query =
		"	SELECT * FROM tab_pessoas
			WHERE (
				lower(email_usuario) = :email_usuario
				AND id_pessoa = id_usuario_sistema
				AND id_pessoa <> :id_usuario
			)
		";

		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
	
		$stmt->bindParam(':email_usuario', $usuario->email_usuario);
		$stmt->bindParam(':id_usuario', $usuario->id_pessoa, PDO::PARAM_INT);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE[0]: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() > 0 ) {
			return erro("Já existe um cadastro com o e-mail '{$usuario->email_usuario}' Verifique e tente novamente.", 400, [$usuario]);
		}
		
		
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$SUBQUERY_CAMPOS_NAO_OBRIGATORIOS = '';
		$campos_nao_obrigatorios = [
			'cep',
			'rg_ie',
			'CPF_CNPJ',
			'nascimento',
			'Numero',
			'bairro',
			'logradouro',
			'complemento',
			'telefone_fixo',
		];

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


		foreach ($usuario as $nome_campo => $valor) {

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
				nome_razao_social				 = :nome_razao_social,
				nome_propriedade_fazenda = :nome_propriedade_fazenda,
				
				telefone_celular = :telefone_celular,
				email_usuario = :email_usuario,
				senha_usuario = :senha_usuario,

				id_estado = :id_estado,
				id_cidade = :id_cidade,

				{$SUBQUERY_CAMPOS_NAO_OBRIGATORIOS}

				DATA_ATUALIZACAO = CURDATE(),
				ID_USUARIO_ATUALIZACAO = :id_usuario
			WHERE (
				id_pessoa = :id_usuario
			)
		";

		$stmt = $connect->prepare($query_update);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

		$stmt->bindParam(':id_usuario', $usuario->id_pessoa, PDO::PARAM_INT);

		$stmt->bindParam(':nome_razao_social', $usuario->nome_razao_social);
		$stmt->bindParam(':nome_propriedade_fazenda', $usuario->nome_propriedade_fazenda);

		$stmt->bindParam(':telefone_celular', $usuario->telefone_celular);
		$stmt->bindParam(':email_usuario', $usuario->email_usuario);
		$stmt->bindParam(':senha_usuario', $usuario->senha_usuario);

		$stmt->bindParam(':id_estado', $usuario->id_estado, PDO::PARAM_INT);
		$stmt->bindParam(':id_cidade', $usuario->id_cidade, PDO::PARAM_INT);
		
		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		

		if ( !modo_dev() ) {
			$connect->commit();	
		}
		return sucesso("CADASTRO ATUALIZADO COM SUCESSO!" . ($stmt->rowCount() <= 0 ? " - NENHUMA INFORMAÇÃO ALTERADA! " : ''));
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
