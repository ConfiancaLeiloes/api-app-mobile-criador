<?php

use Psr\Http\Message\ServerRequestInterface;

class UsuarioModel extends PessoaModel
{

	

	/**
	 * Método plano_gratis()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return object
	*/
	private function plano_gratis()
	{

		$num_dias_plano_gratis = 5;
		$data_final_plano_gratis = date('Y-m-d', strtotime("+{$num_dias_plano_gratis} days", strtotime(DATA_ATUAL)));
		$data_final_plano_gratis_format = date('d/m/Y', strtotime($data_final_plano_gratis));

		return (object)[
			'num_dias_plano_gratis' 				 => $num_dias_plano_gratis,
			'data_final_plano_gratis' 			 => $data_final_plano_gratis,
			'data_final_plano_gratis_format' => $data_final_plano_gratis_format
		];

	}


	/**
	 * Método login()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
	// public function cadastro($login) {
	public function login(ServerRequestInterface $request) {

		$login = (object)$request->getParsedBody();	
		
		if ( !isset($login->email) ) {
			return erro("Campo [E-MAIL] não informado!");
		}
		if ( !isset($login->senha) ) {
			return erro("Campo [SENHA] não informado!");
		}

		if ( vazio($login->email) ) {
			return erro("Informe o [E-MAIL]!");
		}
		if ( vazio($login->senha) ) {
			return erro("Informe a [SENHA]!");
		}

		if ( !valida_email($login->email) ) {
			return erro("[E-MAIL] INVÁLIDO!");
		}
		if ( strlen($login->senha) < 4 ) {
			return erro("[SENHA] INVÁLIDA!");
		}


	

		$connect = $this->conn->conectar();

		$query = 
		"	SELECT
				-- tab_pessoas.id_pessoa,
				tab_pessoas.id_situacao,
				tab_proprietario.data_limite_licenca,
				tab_pessoas.id_pessoa AS ID_USUARIO,
				tab_pessoas.id_usuario_sistema AS ID_PROPRIETARIO,
				substring_index(tab_pessoas.nome_razao_social, ' ', 1) as PRIMEIRO_NOME_USUARIO
				
			FROM tab_fazendas_usuario 
			JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_fazendas_usuario.id_usuario
			JOIN tab_pessoas AS tab_proprietario ON (
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

		// $usuario->TOKEN = md5(DATA_HORA_ATUAL . $usuario->id_pessoa) .'-'. cripto($usuario->ID_PROPRIETARIO) .'-'. cripto(strtotime(DATA_HORA_ATUAL));
		// $usuario->TOKEN = md5(DATA_HORA_ATUAL . $usuario->ID_USUARIO) .'-'. cripto(strtotime(DATA_HORA_ATUAL));
		$usuario->TOKEN = md5(DATA_HORA_ATUAL . $usuario->ID_USUARIO);

		$connect->beginTransaction();


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		# ATUALIZANDO O TOKEN
		$query_update_token =
		"	UPDATE tab_pessoas SET
				token_login_app = :TOKEN
			WHERE (
				id_pessoa = :id_pessoa AND 
				id_usuario_sistema = :id_proprietario
			)
		";
		$stmt = $connect->prepare($query_update_token);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		$stmt->bindParam(':TOKEN', $usuario->TOKEN);
		$stmt->bindParam(':id_pessoa', $usuario->ID_USUARIO);
		$stmt->bindParam(':id_proprietario', $usuario->ID_PROPRIETARIO);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Não foi possível atualizar o token! Verifique e tente novamente", 404);
		}


		$login->plataforma = $body->plataforma == 'ios' ? 102 : 101;

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		# REGISTRANDO O LOG DE ACESSO
		$query_insert_log_login =
		"	INSERT INTO tab_log_login  (
				id_usuario_sistema,
				
				data, hora,
				
				tipo,
				ip,
				versao                                        
			) 
			VALUES (
				'{$usuario->ID_USUARIO}',
				
				CURDATE(), CURTIME(),
				
				'{$login->plataforma}',
				'{$_SERVER['REMOTE_ADDR']}',
				'app'
			)
		";
		$stmt = $connect->prepare($query_insert_log_login);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		unset($usuario->id_situacao);
		unset($usuario->data_limite_licenca);

		$connect->commit();
		return sucesso("Login Realizado com Sucesso!", [$usuario]);
	}










	/**
	 * Método token_valido()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return boolean
	*/
	public function token_valido($post) {

		$connect = $this->conn->conectar();
		
		// Faz a consulta e verifica se o Token existe ativo para o usuário
		$query_token = 
		"	SELECT id_pessoa, token_login_app FROM tab_pessoas
			WHERE	(
				token_login_app = '{$post->token}' AND 
				id_usuario_sistema = '{$post->id_usuario}'
			)
		";
		$stmt = $connect->prepare($query_token);
		if(!$stmt) {
			return false;
		}
		// $stmt->bindParam(":id_pessoa", $id_pessoa, PDO::PARAM_INT);
		
		if( !$stmt->execute() ) {
			return false;
		}

		return $stmt->rowCount() > 0;
	}

	




	public function tem_permissao_acesso($id_usuario = 0, $id_modulo = 0) {

		$connect = $this->conn->conectar();

		$query =
		"	SELECT
				id_privilegio_aplicativo, 
				tab_privilegios_usarios_aplicativo.id_usuario_fazenda, 
				id_modulo
			FROM tab_pessoas
			JOIN tab_fazendas_usuario ON tab_fazendas_usuario.id_usuario = tab_pessoas.id_pessoa
			JOIN tab_privilegios_usarios_aplicativo ON
				tab_privilegios_usarios_aplicativo.id_usuario_fazenda = tab_fazendas_usuario.id_usuario_fazenda
			WHERE (
				id_pessoa = '$id_usuario'
				AND id_modulo = '$id_modulo'
			)
		";

		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return false;
		}
		if( !$stmt->execute() ) {
			return false;
		}

		return $stmt->rowCount() > 0;
	}
	














	/**
	 * Método cadastro()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function cadastro(ServerRequestInterface $request) {

		// $post = body_params();
		$post = (object)$request->getParsedBody();

		if ( isset($post->id_pessoa) && ((int)$post->id_pessoa <= 0 || is_null($post->id_pessoa) || vazio($post->id_pessoa) ) ) {
			// return erro('Identificação de Usuário inválida!');
		}

		# VALIDANDO {{NÃO}} CAMPOS OBRIGATÓRIOS
		if ( !vazio($post->CPF_CNPJ) ) {

			if ( !cpf_cnpj_valido($post->CPF_CNPJ) ) {
				return erro('Campo [CPF / CNPJ] inválido!');
			}
			
			$post->CPF_CNPJ = somente_numeros($post->CPF_CNPJ);
		}

		if ( isset($post->nascimento) && !vazio($post->nascimento) && !data_valida($post->nascimento) ) {
			return erro('Campo [DATA DE NASCIMENTO] inválida!');
		}

		if ( isset($post->nascimento) && strtotime($post->nascimento) > strtotime(DATA_ATUAL) ) {
			return erro('Campo [DATA DE NASCIMENTO] inválida! (data futura)');
		}

		if ( isset($post->cep) && !vazio($post->cep) ) {
			if ( strlen($post->cep) < 8 ) {
				return erro("Campo [CEP] inválido!");
			}
		}

		if ( strlen($post->telefone_fixo) > 0 && strlen($post->telefone_fixo) < 10) {
			return erro('Campo [TELEFONE] inválido!');
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
			return erro("Campo [NOME / RAZÃO SOCIAL] não informado!");
		}

		if ( vazio($post->nome_propriedade_fazenda) ) {
			return erro("Campo [NOME DO HARAS / FAZENDA] não informado!");
		}
		
		if ( vazio($post->email_usuario) ) {
			return erro("Campo [E-MAIL] não informado!");
		}

		if ( !valida_email($post->email_usuario) ) {
			return erro("[E-MAIL] INVÁLIDO!");
		}

		$post->email_usuario = strtolower($post->email_usuario);

		if ( vazio($post->senha_usuario) ) {
			return erro("Campo [SENHA] não informado!");
		}
		if ( strlen($post->senha_usuario) < 6 ) {
			return erro("Campo [SENHA] inválido!");
		}

		if ( vazio($post->telefone_celular) ) {
			return erro("Campo [CELULAR] não informado!");
		}



		if ( !valida_celular($post->telefone_celular) ) {
			return erro("Número de [CELULAR] INVÁLIDO!");
		}

		if ( (int)$post->id_cidade <= 0 ) {
			return erro("Campo [CIDADE] não informado!");
		}

		if ( (int)$post->id_estado <= 0 ) {
			return erro("Campo [ESTADO / UF] não informado!");
		}

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		$connect = $this->conn->conectar();
		$query =
		"	SELECT 
			(
				SELECT nome_cidade FROM tab_cidades WHERE id_cidade = '{$post->id_cidade}'
			) AS nome_cidade,
			(
				SELECT sigla_estado FROM tab_estados WHERE id_estado = '{$post->id_estado}'
			) AS sigla_estado
		";
		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
		if( !$stmt->execute() ) {
			return erro('error', 'Erro: SQLSTATE: '. $stmt->errorInfo()[modo_dev() ? 2 : 1]);
		}
		$local = $stmt->fetch(PDO::FETCH_OBJ);

		if ( vazio($local->nome_cidade) ) {
			return erro("Campo [ESTADO / UF] não identificado!");
		}
		if ( vazio($local->nome_cidade) ) {
			return erro("Campo [CIDADE] não identificado!");
		}

		$post->nome_cidade = $local->nome_cidade;
		$post->sigla_estado = $local->sigla_estado;
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
		else {
			return $this->insert($post);
		}

	}

	







	/**
	 * Método insert()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	private function insert($usuario) {

		$connect = $this->conn->conectar();
		$plano_gratis = $this->plano_gratis();

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

				upper(:nome_razao_social),
				upper(:nome_propriedade_fazenda),
				
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
				upper(:Numero),
				upper(:bairro),
				upper(:logradouro),
				upper(:complemento),

				-- [data_limite_licenca] -> 5 DIAS A PARTIR DA DATA DO CADASTRO',
				-- DATE_ADD(curdate(), INTERVAL 5 DAY),
				:data_limite_licenca,

				-- [informacoes_diversas]
				'Usuário cadastrado via API do App Mobile',

				'1', -- ATIVO [id_situacao] -> PROVISÓRIO
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

		$stmt->bindParam(':data_limite_licenca', $plano_gratis->data_final_plano_gratis);



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
			return erro('error', 'Erro: SQLSTATE: '. $stmt->errorInfo()[1]); // http://us3.php.net/pdo.errorInfo	
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
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		// echo $this->msg_confirmacao_cadastro($usuario, 2); exit;

		@dispara_email($this->msg_confirmacao_cadastro($usuario, 2), 'NOVO CADASTRO - APLICATIVO!', EMAIL_CADASTRO);	

		// sleep(2);
		@dispara_email($this->msg_confirmacao_cadastro($usuario, 1), 'CONFIRMAÇÃO DE CADASTRO!', $usuario->email_usuario);
		
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
	private function update($usuario) {

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
		
		$connect->commit();	
		return sucesso("CADASTRO ATUALIZADO COM SUCESSO!" . ($stmt->rowCount() <= 0 ? " - NENHUMA INFORMAÇÃO ALTERADA! " : ''));
	}







	/**
	 * Método recuperar_senha()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function recuperar_senha($get) {
		
		if ( !isset($get->email) ) {
			return erro("Campo [E-MAIL] não informado!");
		}

		if ( vazio($get->email) ) {
			return erro("Informe o [E-MAIL]!");
		}

		if ( !valida_email($get->email) ) {
			return erro("[E-MAIL] INVÁLIDO!");
		}



		$connect = $this->conn->conectar();

		# PESQUISANDO O E-MAIL NO BANCO
		$query =
		"	SELECT 
				tab_pessoas.email_usuario,
				tab_pessoas.id_pessoa AS ID_USUARIO,
				tab_pessoas.nome_razao_social AS NOME_USUARIO
			FROM tab_fazendas_usuario 
			JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_fazendas_usuario.id_usuario
			JOIN tab_pessoas AS tab_proprietario ON (
				tab_proprietario.id_pessoa = tab_fazendas_usuario.id_fazenda 
			)
			WHERE (
				length(tab_pessoas.email_usuario) > 5
				AND tab_pessoas.email_usuario = :email
			)
		";

		$stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
	
		$stmt->bindParam(':email', $get->email);
		
		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Nenhum usuário com o e-mail '{$get->email}' encontrado! Verifique e tente novamente.", 404);
		}
		if ( $stmt->rowCount() > 1 ) {
			return erro("Cadastro em duplicidade com o e-mail '{$get->email}'! Entre em contato com a Confiança!", 409);
		}

		$usuario = $stmt->fetch(PDO::FETCH_OBJ);


		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$query_update =
		"	UPDATE tab_pessoas SET
				token_login_app = null,
				senha_usuario = :nova_senha 
			WHERE (
				id_pessoa = :id_usuario AND
				email_usuario = :email
			)
		";
		
		$stmt = $connect->prepare($query_update);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}
	
		$NOVA_SENHA = rand(100000, 999999);

		$stmt->bindParam(':id_usuario', $usuario->ID_USUARIO);
		$stmt->bindParam(':nova_senha', $NOVA_SENHA);
		$stmt->bindParam(':email', $get->email);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 2 : 1 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Erro ao gerar nova senha!");
		}

		# DISPARANDO O E-MAIL
		$MENSAGEM = '<br>SEGUE ABAIXO SUA NOVA SENHA DE ACESSO!';
		$MENSAGEM .= "<br>NOVA SENHA: <b>{$NOVA_SENHA}</b>";

		if ( !@dispara_email($MENSAGEM, 'RECUPERAÇÃO DE SENHA', $get->email) ) {
			return erro("Erro ao disparar e-mail");
		}
		
		return sucesso("Uma nova senha foi gerada e enviada ao seu e-mail!");
		
	} # recuperar_senha()









	/**
	 * Método msg_confirmacao_cadastro()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return String
	*/
	private function msg_confirmacao_cadastro($usuario, $tipo = 1) {

		$DATA_HORA_ATUAL = date('d/m/Y - H:i', strtotime(DATA_HORA_ATUAL));

		$plano_gratis = $this->plano_gratis();
		$num_dias_plano_gratis = $plano_gratis->num_dias_plano_gratis;
		$data_final_plano_gratis_format = $plano_gratis->data_final_plano_gratis_format;

		$usuario->nome_razao_social = mb_strtoupper($usuario->nome_razao_social, 'UTF-8');
		$usuario->nome_propriedade_fazenda = mb_strtoupper($usuario->nome_propriedade_fazenda, 'UTF-8');
		$usuario->numero_animais_plantel = (int)$usuario->numero_animais_plantel;

		$mensagem1 =
		" <div style='margin-top: 30px;'>
				<h1 style='color: #EC6608; margin-bottom: 0'> Olá, {$usuario->nome_razao_social}! </h1>
				<h3 style='margin-top: 0;'>Seja muito bem-vindo ao Confiança Criador, <br> a plataforma mais completa para a gestão de seu haras.</h3>
				<p class='margin-top: 30px;'>Você se cadastrou em nosso aplicativo e forneceu as seguintes informações:</p>
			</div>
	
			<div style='width:100%; margin-top: 30px;'>
				<h4> 1 - Plano escolhido:</h4>
				<p style='margin: 3px;'>Plano:    	 <b style='color: #EC6608'>GRÁTIS - PERÍODO DE TESTES</b></p>
				<p style='margin: 3px;'>Período:  	 <b>{$num_dias_plano_gratis} dias</b> (Até {$data_final_plano_gratis_format})</p>
				<p style='margin: 3px;'>Contratação: <b>{$DATA_HORA_ATUAL}</b></p>
			</div>
	
			<div style='width:100%; margin-top: 30px;'>
				<h4>2 - Dados cadastrais:</h4>
				<p style='margin: 3px;'>Nome:          <b style='color: #EC6608'>{$usuario->nome_razao_social}</b></p>
				<p style='margin: 3px;'>Fazenda/Haras: <b>{$usuario->nome_propriedade_fazenda}</b></p>
				<p style='margin: 3px;'>Plantel:       <b>{$usuario->numero_animais_plantel} Animais</b></p>
				<p style='margin: 3px;'>Cidade / UF:   <b>{$usuario->nome_cidade}/{$usuario->sigla_estado}</b></p>
				<p style='margin: 3px;'>Celular:       <b>{$usuario->telefone_celular}</b></p>
			</div>

			<div style='width:100%; margin-top: 30px;'>
				<h4> 3 - Dados de Acesso ao Sistema e Aplicativo:</h4>
				<p style='margin: 3px'>E-mail: <b style='color: #EC6608'>{$usuario->email_usuario}</b></p>
				<p style='margin: 3px'>Senha:  <b style='color: #EC6608'>{$usuario->senha_usuario}</b></p>
			</div>

			<div style='width:100%; margin-top: 30px;'>
				<h4>4 – Download do Confiança Criador Office:</h4>
				<a href='https://confiancacriador.digital/download/Setup_Confianca_Criador.exe' target='_blank'>
					<img src='https://confiancacriador.digital/assets/img/email/download.svg' />
				</a>
	
				<h4>5 – Download do Aplicativo Confiança Criador nas lojas:</h4>
				<a href='#'>
					<img src='https://confiancacriador.digital/assets/img/email/apple.svg' />
				</a>
				<a href='#'>
					<img src='https://confiancacriador.digital/assets/img/email/android.svg' />
				</a>
			</div>
	
			<div style='width:100%; margin-top: 30px;'>
				<h4>6 – Nossos Termos de Uso e Políticas de Privacidade:</h4>
				<a  href='https://confiancacriador.digital/termos/termos_de_uso.pdf' target='_blank' style='text-decoration:none;'>
					<span style='width: 100px; height: 30px; padding: 10px 30px; border-radius: 10px; text-align:center; background-color: #1A1A1A; color: #ffffff;'> Baixar / Visualizar </span>
				</a>
			</div>
			<br>
		";

	


		# MENSAGEM PARA O ADMINSTRADOR CONFIANCA
		$mensagem2 =
		"	<div style='margin-top: 30px;'>
				<h1 style='color: #EC6608; margin-bottom: 0'> OLÁ CONFIANÇA!</h1>
				<h3 style='margin-top: 0;'>Recebemos um novo cadastro no Confiança Criador.</h3>
				<p class='margin-top: 30px;'>O cliente: {$nome_usuario} se cadastrou pelo aplicativo e forneceu as seguintes informações:</p>
			</div>
		
			<div style='width:100%; margin-top: 30px;'>
				<h4> 1 - Plano escolhido:</h4>
				<p style='margin: 3px;'>Plano:    	 <b style='color: #EC6608'>GRÁTIS - PERÍODO DE TESTES</b></p>
				<p style='margin: 3px;'>Período:  	 <b>{$num_dias_plano_gratis} dias</b> (Até {$data_final_plano_gratis_format})</p>
				<p style='margin: 3px;'>Contratação: <b>{$DATA_HORA_ATUAL}</b></p>
			</div>
		
			<div style='width:100%; margin-top: 30px;'>
				<h4>2 - Dados cadastrais:</h4>
				<p style='margin: 3px;'>Nome:          <b style='color: #EC6608'>{$usuario->nome_razao_social}</b></p>
				<p style='margin: 3px;'>Fazenda/Haras: <b>{$usuario->nome_propriedade_fazenda}</b></p>
				<p style='margin: 3px;'>Plantel:       <b>{$usuario->numero_animais_plantel} Animais</b></p>
				<p style='margin: 3px;'>Cidade / UF:   <b>{$usuario->nome_cidade}/{$usuario->sigla_estado}</b></p>

				<p style='margin: 3px;'>E-mail:  <b>{$usuario->email_usuario}</b></p>
				<p style='margin: 3px;'>Celular: <b>{$usuario->telefone_celular}</b></p>
			</div>
		";

		

		return $tipo == 1 ? $mensagem1 : $mensagem2;
	} # msg_confirmacao_cadastro



} # class UsuarioModel
