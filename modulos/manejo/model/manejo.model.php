<?php
use Psr\Http\Message\ServerRequestInterface;

class ManejoModel
{
    private $conn;
    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }
    public function listar_movimentacoes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $palavra_chave      = $params['palavra_chave'];
        $data_inicial       = $params['data_inicial'];
        $data_final         = $params['data_final'];
        $tipo_movimentacao  = $params['tipo_movimentacao'];

        if (!$tipo_movimentacao || !$data_inicial || !$id_proprietario) return erro("Parâmetros inválidos ou faltantes!");

        try {

            // Define o Grupo
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 1 ? "" : "" ;
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 2 ? " tab_entradas_saidas_animais.id_situacao_movimento = '30' AND " : $filtro_tipo_movimentacao;
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 3 ? " tab_entradas_saidas_animais.id_situacao_movimento = '31' AND " : $filtro_tipo_movimentacao;
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 4 ? " tab_entradas_saidas_animais.id_situacao_movimento = '80' AND " : $filtro_tipo_movimentacao;

            $query_sql = 
                        "SELECT  
                        tab_entradas_saidas_animais.id_entrada_saida_animal as ID_MOVIMENTACAO,
                        DATE_FORMAT(tab_entradas_saidas_animais.data_movimento, '%d/%m/%Y') as DATA_SANITARIO,
                        tab_animais.id_animal as ID_ANIMAL_MOVIMENTACAO,
                        UPPER(tab_animais.nome) as NOME_ANIMAL_MOVIMENTACAO,
                        UPPER(tab_sexos.sexo_animal) as SEXO_ANIMAL_MOVIMENTACAO,
                        UPPER(tab_grupo_animais.descricao) as GRUPO_ANIMAL_MOVIMENTACAO,
                        UPPER(tab_local_origem.descricao) as LOCAL_ORIGEM_MOVIMENTACAO,
                        IF(ISNULL(tab_pessoa_origem.nome_razao_social),'NÃO INFORMADO',tab_pessoa_origem.nome_razao_social) as PESSOA_ORIGEM_MOVIMENTACAO,
                        tab_local_destino.descricao as LOCAL_DESTINO_MOVIMENTACAO,
                        IF(ISNULL(tab_pessoa_destino.nome_razao_social),'NÃO INFORMADO',tab_pessoa_destino.nome_razao_social) as PESSOA_DESTINO_MOVIMENTACAO, 
                        IF(ISNULL(tab_transportador.nome_razao_social),'NÃO INFORMADO',CONCAT(UPPER(tab_transportador.nome_razao_social),'\nTelefone: ',IF(ISNULL(tab_transportador.telefone_celular) OR TRIM(tab_transportador.telefone_celular) = '','SEM NÚMERO',tab_transportador.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_transportador.email_usuario) OR TRIM(tab_transportador.email_usuario) = '','SEM E-MAIL',tab_transportador.email_usuario))) as TRANSPORTADOR_MOVIMENTACAO,
                        UPPER(tab_situacoes.descricao) as TIPO_MOVIMENTACAO
                    FROM tab_entradas_saidas_animais 
                        JOIN tab_animais_movimentacoes ON tab_animais_movimentacoes.id_entrada_saida_animais = tab_entradas_saidas_animais.id_entrada_saida_animal
                        JOIN tab_animais ON tab_animais.id_animal = tab_animais_movimentacoes.id_animal
                        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_entradas_saidas_animais.id_situacao_movimento
                        JOIN tab_localizacoes as tab_local_origem ON tab_local_origem.id_localizacao = tab_entradas_saidas_animais.id_local_origem
                        JOIN tab_localizacoes as tab_local_destino ON tab_local_destino.id_localizacao = tab_entradas_saidas_animais.id_local_destino
                        LEFT JOIN tab_pessoas as tab_pessoa_origem ON tab_pessoa_origem.id_pessoa = tab_entradas_saidas_animais.id_pessoa_remetente   
                        LEFT JOIN tab_pessoas as tab_pessoa_destino ON tab_pessoa_destino.id_pessoa = tab_entradas_saidas_animais.id_pessoa_receptor 
                        LEFT JOIN tab_pessoas as tab_transportador ON tab_transportador.id_pessoa = tab_entradas_saidas_animais.id_pessoa_transportador
                    WHERE
                        $filtro_tipo_movimentacao
                        tab_entradas_saidas_animais.data_movimento BETWEEN '$data_inicial' AND '$data_final' AND
                        ( 
                            tab_animais.nome LIKE '%$palavra_chave%' OR  
                            tab_animais.marca LIKE '%$palavra_chave%' OR  
                            tab_animais.registro_associacao LIKE '%$palavra_chave%' OR    
                            tab_animais.chip LIKE '%$palavra_chave%' OR    
                            tab_animais.informacoes_diversas LIKE '%$palavra_chave%' OR
                            tab_local_origem.descricao LIKE '%$palavra_chave%' OR
                            tab_local_destino.descricao LIKE '%$palavra_chave%' OR
                            tab_pessoa_origem.nome_razao_social LIKE '%$palavra_chave%' OR
                            tab_pessoa_destino.nome_razao_social LIKE '%$palavra_chave%' OR
                            tab_transportador.nome_razao_social LIKE '%$palavra_chave%'
                        ) AND 
                        tab_entradas_saidas_animais.id_usuario_sistema = :ID_PROPRIETARIO
                    ORDER BY tab_entradas_saidas_animais.data_movimento ASC";

            $connect = $this->conn->conectar();

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Nenhuma Movimentação foi localizada!");

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            $entradas = 0;
            $saidas   = 0;
            $interna  = 0;
            foreach ($dados as $key => $value) {
                // Soma as Entradas
                $entradas = trim($value['TIPO_MOVIMENTACAO']) == "ENTRADA" ? $entradas + 1: $entradas;
                $saidas = trim($value['TIPO_MOVIMENTACAO'])   == "SAÍDA"   ? $saidas   + 1: $saidas;
                $interna = trim($value['TIPO_MOVIMENTACAO'])  == "INTERNA" ? $interna  + 1: $interna;
                
                $dados[$key]['CONTADOR'] =  $key+1;
            }
            $somatorio = [
                "TOTAL_GERAL_MOVIMENTACOES" => (int)$key+1,
                "TOTAL_ENTRADAS" => (int)$entradas,
                "TOTAL_SAIDAS" => (int)$saidas,
                "TOTAL_INTERNAS" => (int)$interna               
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }




    public function listar_locais(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $palavra_chave      = $params['palavra_chave'];

        if (!$id_proprietario) return erro("Parâmetros inválidos ou faltantes!");

        try {

            $query_sql = 
            "   SELECT  
                    tab_localizacoes.id_localizacao as ID_LOCAL, 
                    tab_localizacoes.descricao as NOME_LOCAL, 
                    tab_localizacoes.lotacao_maxima as LOTACAO_MAXIMA_LOCAL,   
                    (
                        COUNT(tab_animais.id_localizacao) - 
                        (
                        SELECT  
                        COUNT(tab_animais.id_animal)  
                        FROM tab_animais  
                            LEFT JOIN tab_compras_vendas_animais ON tab_compras_vendas_animais.id_produto_animal = tab_animais.id_animal  
                        WHERE  
                            tab_animais.id_localizacao = tab_localizacoes.id_localizacao  
                            AND tab_compras_vendas_animais.id_situacao_recebimento_entrega = '39'  
                            AND tab_compras_vendas_animais.id_tipo_produto = '1'
                            AND tab_animais.id_situacao = '1' 
                        )
                    ) AS TOTAL_GERAL_ANIMAIS_LOCAL  
                FROM tab_localizacoes  
                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_localizacoes.id_situacao  
                LEFT JOIN tab_animais ON (
                    tab_animais.id_localizacao = tab_localizacoes.id_localizacao  
                    AND tab_animais.id_situacao_cadastro = '11' 
                    AND tab_animais.id_situacao_vida = '15' 
                    AND tab_animais.id_situacao = '1'
                )
                WHERE  (
                    tab_localizacoes.id_situacao = '1' AND  
                    ( 
                        tab_localizacoes.descricao LIKE '%$palavra_chave%' OR  
                        tab_localizacoes.informacao_adicional LIKE '%$palavra_chave%' 
                    ) AND  
                    tab_localizacoes.id_usuario_sistema = :ID_PROPRIETARIO
                )
                GROUP BY tab_localizacoes.id_localizacao  
                ORDER BY tab_localizacoes.descricao ASC
            ";


            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("NENHUM RESULTADO ENCONTRADO!", 404);

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            $totalizador = 0;
            foreach ($dados as $key => $value) {
                // Faz o Somatório dos Animais por Locais
                $totalizador = $totalizador + $value['TOTAL_GERAL_ANIMAIS_LOCAL'];
                
                $dados[$key]['CONTADOR'] =  $key+1;
            }
            $somatorio = [
                "TOTAL_ANIMAIS_LOCAIS" => $totalizador             
            ];



            return sucesso("{$res->rowCount()} REGISTROS ENCONTRADOS!", ["dados"=>$dados, "resumo"=> $somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }







    public function listar_lotes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $palavra_chave      = $params['palavra_chave'];

        if (!$id_proprietario) return erro("Parâmetros inválidos ou faltantes!");

        try {

            $query_sql = 
                        "SELECT  
                        tab_lotes.id_lote as ID_LOTE, 
                        tab_lotes.descricao as NOME_LOTE,  
                        COUNT(tab_animais_nos_lotes.id_animal) AS TOTAL_ANIMAIS_LOTE  
                    FROM tab_lotes  
                    JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_lotes.id_situacao  
                    LEFT JOIN tab_animais_nos_lotes ON tab_animais_nos_lotes.id_lote = tab_lotes.id_lote  
                    WHERE  
                    tab_lotes.id_situacao = '1' AND
                        (
                            tab_lotes.descricao LIKE '%$palavra_chave%' OR  
                            tab_lotes.info_adicional LIKE '%$palavra_chave%' 
                        ) AND  
                        tab_lotes.id_usuario_sistema = :ID_PROPRIETARIO 
                    GROUP BY tab_lotes.id_lote  
                    ORDER BY tab_lotes.descricao ASC";


            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Nenhum Lote foi localizado!");

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            $totalizador = 0;
            foreach ($dados as $key => $value) {
                // Faz o Somatório dos Animais por Locais
                $totalizador = $totalizador + $value['TOTAL_ANIMAIS_LOTE'];
                
                $dados[$key]['CONTADOR'] =  $key+1;
            }
            $somatorio = [
                "TOTAL_ANIMAIS_LOTE" => $totalizador             
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }





    /**
	 * Método cadastro_localizacao()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
	public function cadastro_localizacao(ServerRequestInterface $request) {
        
        $post = (object)$request->getParsedBody();

        if( vazio($post->descricao) ) return erro("Campo [DESCRIÇÃO] Obrigatório!");
        if( strlen($post->descricao) < 3 ) return erro("Campo [DESCRIÇÃO] inválido!");

        if( 
            strlen($post->lotacao_maxima) < 1
            || !is_numeric($post->lotacao_maxima)
            || (int)$post->lotacao_maxima < 0
        )  {
            return erro("Campo [LOTAÇÃO MÁXIMA] inválido!");
        }

        if( !in_array($post->id_arrendado, [10, 20]) ) return erro("Campo [ARRENDADO] inválido!");
        if( !in_array($post->id_situacao, [1, 2]) ) return erro("Campo [SITUAÇÃO] inválido!");

        if( 
            (int)$post->id_filial < 0 ||
            !vazio($post->id_filial) &&
            !is_numeric($post->id_filial)
        )  {
            return erro("Campo [FILIAL] inválido!");
        }

        $post->id_filial = !vazio($post->id_filial) ? $post->id_filial : 0;



        $connect = $this->conn->conectar();
        $query =
        "   SELECT id_localizacao FROM tab_localizacoes
            WHERE (
                descricao = :descricao AND
                id_usuario_sistema = :id_usuario_sistema
            )
        ";
        $stmt = $connect->prepare($query);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

        $stmt->bindParam(':descricao', $post->descricao);
        $stmt->bindParam(':id_usuario_sistema', $post->id_proprietario, PDO::PARAM_INT);

        if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[2], 500);
		}
		if ( $stmt->rowCount() > 0 ) {
			return erro("LOCALIZAÇÃO NÃO CADASTRADA - Já existe um local cadastrado com a descrição '{$post->descricao}'!");
		}



        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
		$connect->beginTransaction();

		$query_insert =
		"  INSERT INTO tab_localizacoes (
                id_filial,
                descricao,
                id_situacao,
                id_arrendado,
                lotacao_maxima,

                id_usuario_sistema,
                informacao_adicional,
                
                DATA_CRIACAO,
                DATA_ATUALIZACAO,

                ID_USUARIO_CRIACAO,
                ID_USUARIO_ATUALIZACAO
			) 
			VALUES (
                :id_filial,
                upper(:descricao),
                :id_situacao,
                :id_arrendado,
                :lotacao_maxima,

                :id_usuario_sistema, -- DONO DO HARAS / FAZENDA / EMPRESA
                :informacao_adicional,
                
                CURDATE(), -- DATA_CRIACAO,
                CURDATE(), -- DATA_ATUALIZACAO,

                :ID_USUARIO_CRIACAO,
                :ID_USUARIO_ATUALIZACAO
			)
		";
		$stmt = $connect->prepare($query_insert);
		if(!$stmt) {
			return erro("Erro: {$connect->errno} - {$connect->error}", 500);
		}

        $stmt->bindParam(':id_filial', $post->id_filial, PDO::PARAM_INT);
        $stmt->bindParam(':descricao', $post->descricao);
        $stmt->bindParam(':id_situacao', $post->id_situacao, PDO::PARAM_INT);
        $stmt->bindParam(':id_arrendado', $post->id_arrendado, PDO::PARAM_INT);
        $stmt->bindParam(':lotacao_maxima', $post->lotacao_maxima, PDO::PARAM_INT);

        $stmt->bindParam(':id_usuario_sistema', $post->id_proprietario, PDO::PARAM_INT);
        $stmt->bindParam(':informacao_adicional', $post->informacao_adicional);

		$stmt->bindParam(':ID_USUARIO_CRIACAO', $post->id_usuario, PDO::PARAM_INT);
		$stmt->bindParam(':ID_USUARIO_ATUALIZACAO', $post->id_usuario, PDO::PARAM_INT);

		if( !$stmt->execute() ) {
			return erro("SQLSTATE: #". $stmt->errorInfo()[ !modo_dev() ? 1 : 2 ], 500);
		}
		if ( $stmt->rowCount() <= 0 ) {
			return erro("Localização não cadastrada!");
		}
        
		msg_debug("LOCALIZAÇÃO [{$connect->lastInsertId()}] CADASTRADA!");
        $connect->commit();
        return sucesso("LOCALIZAÇÃO CADASTRADA COM SUCESSO!", $post);
    }



}