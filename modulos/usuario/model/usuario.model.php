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
	 * Método valida_token()
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
	 * Método perfil()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	// public function perfil($id_pessoa) {
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

		return sucesso("Usuário encontrado! -> {$usuario->nome_razao_social}", $usuario);
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

		if ( strlen($post->telefone_fixo) > 0 && strlen($post->telefone_fixo) < 8) {
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
			return erro("Campo [NOME NO HARAS / FAZENDA] não informado!");
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
				DATE_ADD(curdate(), INTERVAL 5 DAY),

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
		# E-MAIL(S)
		$mensagem = "
			<b>UM NOVO CADASTRO FOI REALIZADO PELO APLICATIVO</b>:
			<br> Nome: {$usuario->nome_razao_social}
			<br> Documento: {$usuario->CPF_CNPJ}
			<br> E-mail: {$usuario->email_usuario}
			<br> Celular: {$usuario->telefone_celular}
		";
		
		!@dispara_email($mensagem, 'NOVO CADASTRO', EMAIL_DEV);
		// !@dispara_email($mensagem, 'NOVO CADASTRO', EMAIL_CONFIANCA);
		
		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$connect->commit();
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
		$MENSAGEM = 'MENSAGEM TESTE DE ENVIO DE E-MAIL';
		if ( !@dispara_email($MENSAGEM, 'RECUPERAÇÃO DE SENHA', $get->email) ) {
			return erro("Erro ao disparar e-mail");
		}
		
		return sucesso("Uma nova senha foi gerada e enviada ao seu e-mail!");
		
	} # recuperar_senha()



} # class UsuarioModel
