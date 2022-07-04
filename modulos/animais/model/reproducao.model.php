<?php
use Psr\Http\Message\ServerRequestInterface;

class ReproducaoModel
{
    private $conn;
    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }

    public function detalhes_reproducao_cobertura(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = @$params['id_proprietario'];
        $id_cobricao        = @$params['id_cobricao'];

        if (!@$id_proprietario || !@$id_cobricao) 
            return erro("Cobertura ou Proprietário com identificação incorreta!");
        
        try {

            $query_sql = 
                        "SELECT 
                        -- Dados da Cobrição
                        tab_cobricoes.id_cobricao as ID_COBRICAO,  
                        DATE_FORMAT(tab_cobricoes.data_cobertura, '%d/%m/%Y') as DATA_COBRICAO,
                        IF((tab_toques.id_situacao_prenhez = '19' AND ISNULL(tab_nascimentos.id_nascimento)),CONCAT(DATEDIFF(CURDATE(), tab_cobricoes.data_cobertura), ' Dia(s)'),'-') as DIAS_GESTACAO_COBRICAO,    
                        tab_central.nome_razao_social as NOME_CENTRAL_COBRICAO,   
                        tab_veterinario.nome_razao_social as NOME_RESPONSAVEL_COBRICAO,  
                        tab_garanhao.id_animal as ID_GARANHAO_COBRICAO,  
                        tab_garanhao.nome as NOME_GARANHAO_COBRICAO,  
                        tab_doadora.id_animal as ID_EGUA_COBRICAO,  
                        tab_doadora.nome as NOME_EGUA_COBRICAO,  
                        tab_receptora.id_animal as ID_RECEPTORA_COBRICAO,  
                        IF(ISNULL(tab_receptora.nome),'-',tab_receptora.nome) as NOME_RECEPTORA_COBRICAO,  
                        IF(ISNULL(tab_receptora.id_animal),'-',DATE_FORMAT(tab_cobricoes.data_te, '%d/%m/%Y')) as DATA_TE_COBRICAO,  
                        UPPER(tab_tipo_gestacao.descricao) as METODO_REPRODUCAO_COBRICAO,  
                        UPPER(tab_tipos_cobricoes.descricao) as TIPO_COBRICAO,  
                        UPPER(tab_tipos_semen.descricao) as TIPO_SEMEN_COBRICAO,  
                        UPPER(tab_disponibilidade.descricao) as DISPONIBILIDADE_EMBRIAO_COBRICAO,
                        IF(ISNULL(tab_cobricoes.informacoes_diversas) OR TRIM(tab_cobricoes.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_cobricoes.informacoes_diversas) as INFORMACAO_ADICIONAL_COBRICAO,
                        -- Dados do Toque
                        IF(ISNULL(tab_toques.data_toque),'AGUARDANDO',DATE_FORMAT(tab_toques.data_toque, '%d/%m/%Y')) as DATA_TOQUE_COBRICAO,
                        IF(ISNULL(tab_situacao_prenhez.descricao),'-',UPPER(tab_situacao_prenhez.descricao)) as RESULTADO_TOQUE_COBRICAO,
                        IF(ISNULL(tab_responsavel_toque.nome_razao_social),'-',tab_responsavel_toque.nome_razao_social) as RESPONSAVEL_TOQUE_COBRICAO,
                        IF(ISNULL(tab_toques.informacoes_diversas) OR TRIM(tab_toques.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_toques.informacoes_diversas) as INFORMACOES_TOQUE_COBRICAO,     
                        -- Dados da Sexagem
                        IF(ISNULL(tab_sexagens.data_sexagem),'-',DATE_FORMAT(tab_sexagens.data_sexagem, '%d/%m/%Y')) as DATA_SEXAGEM_COBRICAO,
                        IF(ISNULL(tab_situacao_sexagem.descricao),'-',tab_situacao_sexagem.descricao) as RESULTADO_SEXAGEM_COBRICAO,
                        IF(ISNULL(tab_responsavel_sexagem.nome_razao_social),'-',tab_responsavel_sexagem.nome_razao_social) as RESPONSAVEL_SEXAGEM_COBRICAO,
                        IF(ISNULL(tab_sexagens.informacoes_diversas) OR TRIM(tab_sexagens.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_sexagens.informacoes_diversas) as INFORMACOES_SEXAGEM_COBRICAO,
                        -- Dados da Comunicação
                        IF(ISNULL(tab_comunicacoes_cobricao_associacao.data_comunicacao),'-',DATE_FORMAT(tab_comunicacoes_cobricao_associacao.data_comunicacao, '%d/%m/%Y')) as DATA_COMUNICACAO_COBRICAO,
                        IF(ISNULL(tab_comunicacoes_cobricao_associacao.protocolo_comunicacao),'-',tab_comunicacoes_cobricao_associacao.protocolo_comunicacao) as PROTOCOLO_COMUNICACAO_COBRICAO,
                        IF(ISNULL(tab_comunicacoes_cobricao_associacao.informacoes_diversas) OR TRIM(tab_comunicacoes_cobricao_associacao.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_comunicacoes_cobricao_associacao.informacoes_diversas) as INFORMACOES_COMUNICACAO_COBRICAO,
                        -- Dados do Nascimento 
                        tab_nascimentos.id_animal_plantel as ID_ANIMAL_PLANTEL_NASCIMENTO_COBRICAO, 
                        IF(ISNULL(tab_nascimentos.nome),'-',tab_nascimentos.nome) as NOME_PRODUTO_NASCIMENTO_COBRICAO,
                        IF(ISNULL(tab_nascimentos.marca),'-',tab_nascimentos.marca) as MARCA_PRODUTO_NASCIMENTO_COBRICAO,
                        IF(ISNULL(tab_sexos.sexo_animal),'-',tab_sexos.sexo_animal) as SEXO_PRODUTO_NASCIMENTO_ANIMAL,
                        IF(ISNULL(tab_nascimentos.data_nascimento),'-',DATE_FORMAT(tab_nascimentos.data_nascimento, '%d/%m/%Y')) as DATA_NASCIMENTO_PRODUTO_COBRICAO,
                        IF(ISNULL(tab_situacao_nascimento.descricao),'-',tab_situacao_nascimento.descricao) as SITUACAO_PRODUTO_NASCIMENTO_COBRICAO,
                        IF(ISNULL(tab_nascimentos.informacoes_diversas) OR TRIM(tab_nascimentos.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_nascimentos.informacoes_diversas) as INFORMACOES_PRODUTO_NASCIMENTO_COBRICAO
                    FROM tab_cobricoes  
                        -- JOINS da Cobrição
                        JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                        JOIN tab_pessoas AS tab_veterinario ON tab_veterinario.id_pessoa = tab_cobricoes.id_veterinario_colaborador  
                        JOIN tab_animais AS tab_garanhao ON tab_garanhao.id_animal = tab_cobricoes.id_animal_macho  
                        JOIN tab_animais AS tab_doadora ON tab_doadora.id_animal = tab_cobricoes.id_animal_femea  
                        JOIN tab_situacoes AS tab_tipo_gestacao ON tab_tipo_gestacao.id_situacao = tab_cobricoes.id_te
                        JOIN tab_tipos_cobricoes ON tab_tipos_cobricoes.id_tipo_cobricao = tab_cobricoes.id_tipo_cobricao
                        JOIN tab_tipos_semen ON tab_tipos_semen.id_tipo_semen = tab_cobricoes.id_tipo_semen
                        JOIN tab_situacoes AS tab_disponibilidade ON tab_disponibilidade.id_situacao = tab_cobricoes.id_disponibilidade
                        LEFT JOIN tab_animais AS tab_receptora ON tab_receptora.id_animal = tab_cobricoes.id_animal_receptora 
                        -- JOINS do Toque
                        LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao
                        LEFT JOIN tab_pessoas AS tab_responsavel_toque ON tab_responsavel_toque.id_pessoa = tab_toques.id_veterinario
                        LEFT JOIN tab_situacoes AS tab_situacao_prenhez ON tab_situacao_prenhez.id_situacao = tab_toques.id_situacao_prenhez
                        -- JOINS da Sexagem
                        LEFT JOIN tab_sexagens ON tab_sexagens.id_cobricao_relacionada = tab_cobricoes.id_cobricao
                        LEFT JOIN tab_pessoas AS tab_responsavel_sexagem ON tab_responsavel_sexagem.id_pessoa = tab_sexagens.id_veterinario
                        LEFT JOIN tab_situacoes AS tab_situacao_sexagem ON tab_situacao_sexagem.id_situacao = tab_sexagens.id_resultado_sexagem
                        -- JOINS da Comunicação de Cobrição
                        LEFT JOIN tab_comunicacoes_cobricao_associacao ON tab_comunicacoes_cobricao_associacao.id_cobricao_relacionada = tab_cobricoes.id_cobricao
                        -- JOINS do Nascimento 
                        LEFT JOIN tab_nascimentos ON tab_nascimentos.id_cobricao = tab_cobricoes.id_cobricao
                        LEFT JOIN tab_situacoes AS tab_situacao_nascimento ON tab_situacao_nascimento.id_situacao = tab_nascimentos.id_situacao_nascimento
                        LEFT JOIN tab_sexos ON tab_sexos.id_sexo = tab_nascimentos.id_sexo
                    WHERE tab_cobricoes.id_cobricao = :ID_COBRICAO AND tab_cobricoes.id_usuario_sistema = :ID_PROPRIETARIO";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->bindValue(':ID_COBRICAO', $id_cobricao);

            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhnuma cobrição ou proprietário encontrado");
            }
            
            return sucesso("Cobrições encontradas: {$res->rowCount()} ",["dados"=>$res->fetchAll(PDO::FETCH_OBJ)]);
            
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }

    public function detalhes_reproducao_nascimento(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = @$params['id_proprietario'];
        $id_nascimento        = @$params['id_nascimento'];

        if (!@$id_proprietario || !@$id_nascimento) 
            return erro("Nascimento ou Proprietário com identificação incorreta!");
        
        try {

            $query_sql = 
                        "SELECT 
                        -- Dados do Nascimento
                        tab_nascimentos.id_nascimento as ID_NASCIMENTO,
                        tab_nascimentos.id_animal_plantel as ID_PLANTEL_NASCIMENTO,
                        tab_nascimentos.nome as NOME_NASCIMENTO,
                        tab_nascimentos.marca as MARCA_NASCIMENTO,    
                        DATE_FORMAT(tab_nascimentos.data_nascimento, '%d/%m/%Y') as DATA_NASCIMENTO,
                        CONCAT(DATEDIFF(tab_nascimentos.data_nascimento, tab_cobricoes.data_cobertura), ' Dia(s)') as DIAS_GESTACAO_NASCIMENTO,   
                        UPPER(tab_sexos.sexo_animal) as SEXO_NASCIMENTO,  
                        UPPER(tab_situacao_nascimento.descricao) as SITUACAO_NASCIMENTO, 
                        tab_garanhao.id_animal as ID_PAI_NASCIMENTO,  
                        tab_garanhao.nome as PAI_NASCIMENTO,
                        tab_doadora.id_animal as ID_MAE_NASCIMENTO,   
                        tab_doadora.nome as MAE_NASCIMENTO,
                        UPPER(tab_classificacoes.nota_classificacao) as CLASSIFICACAO_NASCIMENTO,
                        tab_nascimentos.informacoes_diversas as INFORMACAO_NASCIMENTO,  
                        -- Dados da Comunicação
                        IF(ISNULL(tab_comunicacoes_nascimento_associacao.data_comunicacao),'-',DATE_FORMAT(tab_comunicacoes_nascimento_associacao.data_comunicacao, '%d/%m/%Y')) as DATA_COMUNICACAO_NASCIMENTO,
                        IF(ISNULL(tab_comunicacoes_nascimento_associacao.protocolo_comunicacao),'-',tab_comunicacoes_nascimento_associacao.protocolo_comunicacao) as PROTOCOLO_COMUNICACAO_NASCIMENTO,
                        tab_comunicacoes_nascimento_associacao.informacoes_diversas as INFORMACOES_COMUNICACAO_NASCIMENTO
                    FROM tab_nascimentos
                        -- JOINS do Nascimento
                        JOIN tab_cobricoes ON tab_cobricoes.id_cobricao = tab_nascimentos.id_cobricao
                        JOIN tab_animais AS tab_garanhao ON tab_garanhao.id_animal = tab_cobricoes.id_animal_macho  
                        JOIN tab_animais AS tab_doadora ON tab_doadora.id_animal = tab_cobricoes.id_animal_femea
                        JOIN tab_situacoes AS tab_situacao_nascimento ON tab_situacao_nascimento.id_situacao = tab_nascimentos.id_situacao_nascimento
                        JOIN tab_sexos ON tab_sexos.id_sexo = tab_nascimentos.id_sexo 
                        LEFT JOIN tab_animais ON tab_animais.id_animal = tab_nascimentos.id_animal_plantel
                        LEFT JOIN tab_classificacoes ON tab_classificacoes.id_classificacao = tab_animais.id_classificacao
                        -- JOINS da Comunicação de Nascimento
                        LEFT JOIN tab_comunicacoes_nascimento_associacao ON tab_comunicacoes_nascimento_associacao.id_nascimento_relacionado = tab_nascimentos.id_nascimento
                    WHERE tab_nascimentos.id_nascimento = :ID_NASCIMENTO AND tab_cobricoes.id_usuario_sistema = :ID_PROPRIETARIO";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->bindValue(':ID_NASCIMENTO', $id_nascimento);
            
            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhnuma nascimento identificado");
            }
            
            return sucesso("{$res->rowCount()} nascimento(s) identificado(s)",["dados"=>$res->fetchAll(PDO::FETCH_OBJ)]);
                        
        } catch (\Throwable $th) {
            return throw new Exception($th);
        }
        
    }
    public function listar_banco_nomes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        
        $sexo               = @$params['sexo'];
        $letra_alfabeto     = @$params['letra_alfabeto'];

        if (!trim(@$sexo) || !trim(@$letra_alfabeto) ) 
            return erro("Parâmetros inválidos ou faltantes!");
        
        try {

            $query_sql = 
                        "SELECT NOME FROM
                        (
                            SELECT  
                                TRIM(tab_banco_nomes.nome) as NOME     
                            FROM tab_banco_nomes  
                            WHERE
                                tab_banco_nomes.nome LIKE '$letra_alfabeto%' AND   
                                tab_banco_nomes.sexo LIKE '$sexo%'
                            ORDER BY RAND()
                            LIMIT 1000
                        ) as tab_lista_nomes
                        ORDER BY NOME ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->query($query_sql);
                
            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhum Nome foi localizado!");
            }
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dados as $key => $value) {
                //Acrescenta contador
                $dados[$key]['CONTADOR'] =  $key+1;
             }
             $contador = ($key +1);
             return sucesso( "Foram encontrados '$contador' nomes com a letra '$letra_alfabeto'!", ["dados"=> $dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function listar_centrais(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario = @$params['id_proprietario'];

        if (!@$id_proprietario) 
        return erro("Parâmetros inválidos ou faltantes!");
        
        try {

            $query_sql = 
                        "SELECT  
                                tab_pessoas.id_pessoa as ID_CENTRAL,
                                tab_pessoas.nome_razao_social as NOME_CENTRAL,  
                                CONCAT('Telefone: ',IF(ISNULL(tab_pessoas.telefone_celular) OR TRIM(tab_pessoas.telefone_celular) = '','SEM NÚMERO',tab_pessoas.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_pessoas.email_usuario) OR TRIM(tab_pessoas.email_usuario) = '','SEM E-MAIL',tab_pessoas.email_usuario)) as CONTATO_CENTRAL, 
                                CONCAT(tab_cidades.nome_cidade,' - ', tab_estados.sigla_estado) as CIDADE_ESTADO_CENTRAL,
                                COUNT(tab_cobricoes.id_cobricao) as TOTAL_COBRICAOES_CENTRAL
                            FROM tab_pessoas  
                                JOIN tab_cobricoes ON tab_cobricoes.id_central_reproducao = tab_pessoas.id_pessoa 
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf   
                            WHERE 
                                tab_cobricoes.id_usuario_sistema = :ID_PROPRIETARIO AND
                                tab_cobricoes.id_situacao = '1'
                            GROUP BY tab_pessoas.id_pessoa  
                            ORDER BY tab_pessoas.nome_razao_social ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);

            $res->bindValue(":ID_PROPRIETARIO", $id_proprietario);

            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhuma Central de Reprodução foi localizada!");
            }
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);    
            $totalizador = 0;
            foreach ($dados as $key => $value) {
                // Consulta as Estações por Centrais
                $query_estacoes = 
                "SELECT 
                    tab_estacao_monta.id_estacao_monta as ID_ESTACAO,
                    tab_estacao_monta.estacao AS NOME_ESTACAO,
                    COUNT(tab_cobricoes.id_cobricao) as TOTAL_ESTACAO
                FROM
                    tab_estacao_monta 
                    JOIN tab_cobricoes ON tab_cobricoes.id_central_reproducao = '" . (int)$value['ID_CENTRAL'] . "'
                WHERE
                    tab_cobricoes.data_cobertura BETWEEN tab_estacao_monta.data_inicial AND tab_estacao_monta.data_final AND
                    tab_cobricoes.id_situacao = '1'
                GROUP BY 
                    tab_estacao_monta.id_estacao_monta
                ORDER BY
                    tab_estacao_monta.id_estacao_monta ASC
                ";

                $res = $pdo->query($query_estacoes);
                $dados_estacoes  = $res->fetchAll(PDO::FETCH_ASSOC); 
                
                // Faz o Somatório dos Resumo
                $totalizador += $value['TOTAL_COBRICAOES_CENTRAL'];
                $dados[$key]['ESTACOES'] = $dados_estacoes;
                //Acrescenta contador
                $dados[$key]['CONTADOR'] =  $key+1;
             }
             $somatorio = ["TOTAL_COBRICOES_CENTRAIS" => $totalizador];
             
            return sucesso("Total de cobrições: $totalizador", ["dados"=> $dados, "resumo"=>$somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }

    public function listar_coberturas(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario     =  @$params['id_proprietario'];
        $palavra_chave       =  @$params['palavra_chave'];
        $estacao             =  @$params['estacao'];
        $central             =  @$params['central'];
        $tipo_gestacao       =  @$params['tipo_gestacao'];
        $situacao_prenhez    =  @$params['situacao_prenhez'];
        $situacao_nascimento =  @$params['situacao_nascimento'];
        $ordenacao           =  @$params['ordenacao'];

        if (!@$id_proprietario || !@$tipo_gestacao || !@$situacao_nascimento || !@$situacao_prenhez) 
        return erro("Parâmetros inválidos ou faltantes!");
        
        try {
            // Define a Estação de Monta
            $filtro_estacao = " tab_cobricoes.data_cobertura BETWEEN " . intevalo_datas_estacoes_monta($estacao) . " AND ";
            
            // Define o Tipo de Gestação
            $filtro_gestacao = (int)$tipo_gestacao == 1 ? "": ""; //todos
            $filtro_gestacao = (int)$tipo_gestacao == 2 ? " tab_cobricoes.id_te = '17' AND ": $filtro_gestacao; // Gestação natural
            $filtro_gestacao = (int)$tipo_gestacao == 3 ? " tab_cobricoes.id_te = '18' AND ": $filtro_gestacao; // TE

            // Define o a Situação da Prenhez
            $filtro_prenhez = (int)$situacao_prenhez == 1 ? " tab_cobricoes.id_disponibilidade = '76' AND " : ""; //todos
            $filtro_prenhez = (int)$situacao_prenhez == 2 ? " tab_cobricoes.id_disponibilidade = '76' AND tab_toques.id_situacao_prenhez = '19' AND " : $filtro_prenhez; //positivo
            $filtro_prenhez = (int)$situacao_prenhez == 3 ? " tab_cobricoes.id_disponibilidade = '76' AND tab_toques.id_situacao_prenhez = '20' AND " : $filtro_prenhez; //negativo
            $filtro_prenhez = (int)$situacao_prenhez == 4 ? " (tab_cobricoes.id_disponibilidade = '76' AND tab_toques.id_situacao_prenhez = '21' OR tab_toques.id_situacao_prenhez IS NULL) AND " : $filtro_prenhez; //aguardando
            $filtro_prenhez = (int)$situacao_prenhez == 5 ? " tab_cobricoes.id_disponibilidade = '77' AND " : $filtro_prenhez; // a coletar

            // Define a Situação do Nascimento
            $filtro_nascimento = (int)$situacao_nascimento == 1 ? "": ""; //todos
            $filtro_nascimento = (int)$situacao_nascimento == 2 ? " NOT tab_nascimentos.id_situacao_nascimento IS NULL AND " : $filtro_nascimento; //nascidos
            $filtro_nascimento = (int)$situacao_nascimento == 3 ? " tab_nascimentos.id_situacao_nascimento IS NULL AND ": $filtro_nascimento; //a nascer

            // Define a Ordenação dos Dados
            $ordena_dados = $ordenacao == 0 ? "ASC" : "ASC"; //default
            $ordena_dados = $ordenacao == 1 ? "DESC" : $ordena_dados;

            // Trata a Central
            $central_reproducao = trim(@$central) == "" ? "" : " tab_central.nome_razao_social LIKE '$central' AND ";

            $query_sql = 
                        "SELECT  
                        tab_cobricoes.id_cobricao as ID_COBRICAO, 
                        DATE_FORMAT(tab_cobricoes.data_cobertura, '%d/%m/%Y') as DATA_COBRICAO, 
                        CONCAT(tab_garanhao.nome, ' x ', tab_doadora.nome) as GARANHAO_DOADORA_COBRICAO, 
                        IF(tab_cobricoes.id_te = '18', DATE_FORMAT(tab_cobricoes.data_te, '%d/%m/%Y'), '-') as DATA_TE_COBRICAO, 
                        IF(ISNULL(tab_receptora.nome),'-',CONCAT(tab_receptora.marca, ' - ', tab_receptora.nome)) as NOME_RECEPTORA_COBRICAO, 
                        (CASE WHEN tab_toques.id_situacao_prenhez IS NULL THEN 'SEM TOQUE' ELSE CONCAT(UPPER(tab_situacao_toque.descricao),' ',DATE_FORMAT(tab_toques.data_toque,'%d/%m/%Y')) END) as TOQUE_COBRICAO, 
                        IF((tab_toques.id_situacao_prenhez = '19' AND ISNULL(tab_nascimentos.id_nascimento)),CONCAT(DATEDIFF(CURDATE(), tab_cobricoes.data_cobertura), ' Dia(s)'),CONCAT('Nasceu: ',UPPER(tab_nascimentos.nome),' ',IF(tab_nascimentos.id_sexo = '2','(M)','(F)'))) as DIAS_GESTACAO_COBRICAO,
                        tab_central.nome_razao_social as NOME_CENTRAL_COBRICAO, 
                        tab_tipos_cobricoes.descricao as TIPO_COBRICAO,
                        (
                            CASE 
                                WHEN tab_toques.id_situacao_prenhez IS NULL OR tab_toques.id_situacao_prenhez = '21' THEN '1' -- Sem Toque
                                WHEN tab_toques.id_situacao_prenhez = '19' THEN '2' -- Positivo
                                WHEN tab_toques.id_situacao_prenhez = '20' THEN '3' -- Negativo
                            END
                        ) as ID_TIPO_TOQUE_COBRICAO, 
                        UPPER(tab_situacoes.descricao) as SITUACAO_COBRICAO, 
                        IF(ISNULL(tab_situacao_sexagens.descricao),'SEM SEXAGEM',UPPER(tab_situacao_sexagens.descricao)) as SEXAGEM_COBRICAO, 
                        IF(ISNULL(tab_comunicacoes_cobricao_associacao.protocolo_comunicacao),'SEM COMUNICAÇÃO',CONCAT(tab_comunicacoes_cobricao_associacao.protocolo_comunicacao,IF((NOT tab_comunicacoes_cobricao_associacao.id_receptora_comunicacao = tab_cobricoes.id_animal_receptora AND NOT tab_comunicacoes_cobricao_associacao.id_receptora_comunicacao IS NULL),CONCAT(' - ', UPPER(tab_receptora_comunicacao.nome)),''))) as PROTOCOLO_COMUNICACAO_COBRICAO,
                        IF(ISNULL(tab_nascimentos.id_nascimento),'SEM NASCIMENTO',CONCAT(UPPER(tab_nascimentos.nome),' ',IF(tab_nascimentos.id_sexo = '2','(M)','(F)'))) as NASCIMENTO_COBRICAO,
                        tab_toques.id_situacao_prenhez AS ID_SITUACAO_PRENHEZ,
                        (
                            CASE 
                                WHEN tab_toques.id_situacao_prenhez IS NULL OR tab_toques.id_situacao_prenhez = '21' THEN '4' -- Sem Toque
                                WHEN tab_toques.id_situacao_prenhez = '19' THEN '2' -- Positivo
                                WHEN tab_toques.id_situacao_prenhez = '20' THEN '3' -- Negativo
                                WHEN tab_cobricoes.id_disponibilidade = '77' THEN '5' -- A coletar
                            END
                        ) as TIPO_SITUACAO_PRENHEZ,
                        (
                            CASE 
                                WHEN NOT tab_nascimentos.id_situacao_nascimento IS NULL THEN '2' -- nascidos
                                WHEN tab_nascimentos.id_situacao_nascimento IS NULL THEN '3' -- Há nascer
                            END
                        ) as SITUACAO_NASCIMENTO,
                        (
                            CASE 
                                WHEN tab_cobricoes.id_te = '17' THEN '2' -- Gestação Natural
                                WHEN tab_cobricoes.id_te = '18' THEN '3' -- TE
                            END
                        ) as ID_TIPO_GESTACAO
                    FROM tab_cobricoes  
                        JOIN tab_animais AS tab_garanhao ON tab_garanhao.id_animal = tab_cobricoes.id_animal_macho  
                        JOIN tab_animais AS tab_doadora ON tab_doadora.id_animal = tab_cobricoes.id_animal_femea  
                        LEFT JOIN tab_animais AS tab_receptora ON tab_receptora.id_animal = tab_cobricoes.id_animal_receptora
                        JOIN tab_tipos_cobricoes ON tab_tipos_cobricoes.id_tipo_cobricao = tab_cobricoes.id_tipo_cobricao
                        JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao   
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_cobricoes.id_situacao  
                        LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao  
                        LEFT JOIN tab_situacoes AS tab_situacao_toque ON tab_situacao_toque.id_situacao = tab_toques.id_situacao_prenhez  
                        LEFT JOIN tab_sexagens ON tab_sexagens.id_cobricao_relacionada = tab_cobricoes.id_cobricao  
                        LEFT JOIN tab_situacoes AS tab_situacao_sexagens ON tab_situacao_sexagens.id_situacao = tab_sexagens.id_resultado_sexagem  
                        LEFT JOIN tab_comunicacoes_cobricao_associacao ON tab_comunicacoes_cobricao_associacao.id_cobricao_relacionada = tab_cobricoes.id_cobricao  
                        LEFT JOIN tab_animais AS tab_receptora_comunicacao ON tab_receptora_comunicacao.id_animal = tab_comunicacoes_cobricao_associacao.id_receptora_comunicacao 
                        LEFT JOIN tab_nascimentos ON tab_nascimentos.id_cobricao = tab_cobricoes.id_cobricao
                    WHERE
                        $filtro_estacao
                        $filtro_gestacao
                        $filtro_prenhez
                        $filtro_nascimento
                        $central_reproducao
                        ( 
                            tab_garanhao.nome LIKE '%$palavra_chave%' OR   
                            tab_doadora.nome LIKE '%$palavra_chave%' OR
                            tab_receptora.nome LIKE '%$palavra_chave%' OR
                            tab_receptora.marca LIKE '%$palavra_chave%' OR
                            tab_cobricoes.informacoes_diversas LIKE '%$palavra_chave%'                
                        ) AND
                        tab_cobricoes.id_usuario_sistema = '$id_proprietario' AND
                        tab_cobricoes.id_situacao = '1' 
                    GROUP BY tab_cobricoes.id_cobricao
                    ORDER BY tab_cobricoes.data_cobertura $ordena_dados";

            $pdo = $this->conn->conectar();
            $res = $pdo->query($query_sql);
            
            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhuma Cobrição foi localizada!");
            }
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);    
            
            $sem_toque = 0;
            $positivo = 0;
            $negativo = 0;
            $nascido = 0;
            $nao_nascido = 0;
            $sem_sexagem = 0;
            $sexado_macho = 0;
            $sexado_femea = 0;
            foreach ($dados as $key => $value) {
                // Soma os Embriões Sem Toque
                trim($value['TOQUE_COBRICAO']) == "SEM TOQUE" ? $sem_toque++ : $sem_toque;

                // Soma os Embriões Sem Toque
                strpos($value['TOQUE_COBRICAO'], "POSITIVO") === 0 ? $positivo++ : $positivo;
                
                // Soma os Embriões com Toque Negativo
                strpos($value['TOQUE_COBRICAO'], "NEGATIVO") === 0 ? $negativo++ : $negativo;
                
                // Soma os Embriões Nascidos e não Nascidos
                trim($value['NASCIMENTO_COBRICAO'] == "SEM NASCIMENTO") ? $nao_nascido++ : $nascido++;
                
                // Soma os Embriões Sexados de Macho, Fêmea e Sem Sexagem
                trim($value['SEXAGEM_COBRICAO'] == "SEM SEXAGEM") ? $sem_sexagem++ : $sem_sexagem;
                trim($value['SEXAGEM_COBRICAO'] == "MACHO") ? $sexado_macho++ : $sexado_macho;
                trim($value['SEXAGEM_COBRICAO'] == "FÊMEA") ? $sexado_femea++ : $sexado_femea;

                //Acrescenta contador
                $dados[$key]['CONTADOR'] =  $key+1;
             }
             $total_geral_cobricoes = (int)$key+1;
             $somatorio = [
                "TOTAL_GERAL_COBRICOES" => (int)$key+1,
                "AGUARDANDO_TOQUE" => (int)$sem_toque,
                "TOQUE_POSITIVO" => (int)$positivo,
                "TOQUE_NEGATIVO" => (int)$negativo,
                "SEM_SEXAGEM" => (int)$sem_sexagem,
                "SEXADO_MACHO" => (int)$sexado_macho,
                "SEXADO_FEMEA" => (int)$sexado_femea,
                "NASCIDOS" => (int)$nascido,
                "NAO_NASCIDOS" => (int)$nao_nascido,
                "ESTACAO_MONTA" => get_estacao_monta($estacao) 
            ];
            return sucesso("Total Geral Cobrições:  $total_geral_cobricoes", ["dados"=> $dados, "resumo"=> $somatorio, "estacao"=> $estacao]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function listar_nascimentos(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario     =  @$params['id_proprietario'];
        $palavra_chave       =  @$params['palavra_chave'];
        $estacao             =  @$params['estacao'];
        $situacao_produto    =  @$params['situacao_produto'];
   

        if (!@$id_proprietario || !@$situacao_produto) 
        return erro( "Parâmetros inválidos ou faltantes!");
        
        try {
             // Não permite que a estação seja menor que 0
            $estacao = $estacao <= 0 ? 0 : $estacao;

            // Define a Estação de Monta
            $filtro_estacao = " tab_cobricoes.data_cobertura BETWEEN " . intevalo_datas_estacoes_monta($estacao) . " AND ";

            // Define a Situação do Produto
            $filtro_situacao = (int)$situacao_produto == 3 ? "" : "" ;
            $filtro_situacao = (int)$situacao_produto == 1 ? " tab_nascimentos.id_situacao_nascimento = '26' AND " : $filtro_situacao;
            $filtro_situacao = (int)$situacao_produto == 2 ? " tab_nascimentos.id_situacao_nascimento = '27' AND " : $filtro_situacao;
            
            $query_sql = 
                        "SELECT  
                        tab_nascimentos.id_nascimento as ID_NASCIMENTO,
                        tab_cobricoes.id_cobricao as ID_COBERTURA_NASCIMENTO, 
                        tab_nascimentos.id_animal_plantel as ID_ANIMAL_PLANETEL_NASCIMENTO, 
                        DATE_FORMAT(tab_nascimentos.data_nascimento, '%d/%m/%Y') as DATA_NASCIMENTO,
                        CONCAT(DATEDIFF(tab_nascimentos.data_nascimento, tab_cobricoes.data_cobertura), ' Dia(s)') as DIAS_GESTACAO_NASCIMENTO,  
                        tab_nascimentos.nome as NOME_PRODUTO_NASCIMENTO, 
                        UPPER(tab_nascimentos.marca) as MARCA_PRODUTO_NASCIMENTO,  
                        UPPER(tab_sexos.sexo_animal) as SEXO_PRODUTO_NASCIMENTO, 
                        UPPER(tab_situacoes.descricao) as SITUACAO_VIDA_NASCIMENTO,  
                        tab_garanhao.nome as PAI_PRODUTO_NASCIMENTO, 
                        tab_doadora.nome as MAE_PRODUTO_NASCIMENTO,  
                        CONCAT(IF(tab_receptora.marca IS NULL OR tab_receptora.marca = '','',CONCAT(tab_receptora.marca,' - ')), tab_receptora.nome) as RECEPTORA_PRODUTO_NASCIMENTO,  
                        IF(ISNULL(tab_comunicacoes_cobricao_associacao.protocolo_comunicacao),'-',tab_comunicacoes_cobricao_associacao.protocolo_comunicacao) as COMUNICACAO_COBRICAO_NASCIMENTO,  
                        IF(ISNULL(tab_comunicacoes_nascimento_associacao.protocolo_comunicacao),'-',tab_comunicacoes_nascimento_associacao.protocolo_comunicacao) as COMUNICACAO_NASCIMENTO_NASCIMENTO,  
                        tab_nascimentos.informacoes_diversas as INFORMACOES_NASCIMENTO ,
                        tab_situacoes.id_situacao AS ID_SITUACAO_PRODUTO  
                    FROM tab_cobricoes  
                        JOIN tab_nascimentos ON tab_nascimentos.id_cobricao = tab_cobricoes.id_cobricao  
                        JOIN tab_animais AS tab_garanhao ON tab_garanhao.id_animal = tab_cobricoes.id_animal_macho  
                        JOIN tab_animais AS tab_doadora ON tab_doadora.id_animal = tab_cobricoes.id_animal_femea  
                        LEFT JOIN tab_animais AS tab_receptora ON tab_receptora.id_animal = tab_cobricoes.id_animal_receptora  
                        LEFT JOIN tab_sexos ON tab_sexos.id_sexo = tab_nascimentos.id_sexo  
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_nascimentos.id_situacao_nascimento  
                        LEFT JOIN tab_comunicacoes_cobricao_associacao ON tab_comunicacoes_cobricao_associacao.id_cobricao_relacionada = tab_cobricoes.id_cobricao  
                        LEFT JOIN tab_comunicacoes_nascimento_associacao ON tab_comunicacoes_nascimento_associacao.id_nascimento_relacionado = tab_nascimentos.id_nascimento
                    WHERE
                        $filtro_estacao
                        $filtro_situacao
                        ( 
                            tab_garanhao.nome LIKE '%$palavra_chave%' OR   
                            tab_doadora.nome LIKE '%$palavra_chave%' OR
                            tab_receptora.nome LIKE '%$palavra_chave%' OR
                            tab_receptora.marca LIKE '%$palavra_chave%' OR
                            tab_nascimentos.informacoes_diversas LIKE '%$palavra_chave%'                    
                        ) AND
                        tab_cobricoes.id_usuario_sistema = '$id_proprietario' AND
                        tab_cobricoes.id_situacao = '1' 
                    GROUP BY tab_nascimentos.id_nascimento
                    ORDER BY tab_nascimentos.data_nascimento ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->query($query_sql);
            
            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhum Nascimento foi localizado!");
            }
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);    
            
            $total_machos = 0;
            $total_machos_vivos = 0;
            $total_femeas = 0;
            $total_femeas_vivas = 0;
            $total_machos_mortos = 0;
            $total_femeas_mortas = 0;
            
            foreach ($dados as $key => $value) {
                
                // Soma os Machos
                trim($value['SEXO_PRODUTO_NASCIMENTO']) == "MACHO" ? $total_machos++ : $total_machos;
                // Soma as Femeas
                trim($value['SEXO_PRODUTO_NASCIMENTO']) == "FÊMEA" ? $total_femeas++ : $total_femeas;
                // Soma os Machos Vivos
                trim($value['SEXO_PRODUTO_NASCIMENTO']) == "MACHO" && trim($value['SITUACAO_VIDA_NASCIMENTO']) == "VIVO" ? $total_machos_vivos++ : $total_machos_vivos;
                // Soma os Machos Mortos
                trim($value['SEXO_PRODUTO_NASCIMENTO']) == "MACHO" && trim($value['SITUACAO_VIDA_NASCIMENTO']) == "MORTO" ? $total_machos_mortos++ : $total_machos_mortos;
                // Soma as Fêmeas Vivas
                trim($value['SEXO_PRODUTO_NASCIMENTO']) == "FÊMEA" && trim($value['SITUACAO_VIDA_NASCIMENTO']) == "VIVO" ? $total_femeas_vivas++ : $total_femeas_vivas;
                // Soma as Fêmeas Mortas
                trim($value['SEXO_PRODUTO_NASCIMENTO']) == "FÊMEA" && trim($value['SITUACAO_VIDA_NASCIMENTO']) == "MORTO" ? $total_femeas_mortas++ : $total_femeas_mortas;  
                //Acrescenta contador
                $dados[$key]['CONTADOR'] =  $key+1;
             
            }
            $total_geral_nascimentos = (int)$key+1;
             $somatorio = [
                "TOTAL_GERAL_NASCIMENTOS" => (int)$key+1,
                "TOTAL_MACHOS" => (int)$total_machos,
                "TOTAL_FEMEAS" => (int)$total_femeas,
                "MACHOS_VIVOS" => (int)$total_machos_vivos,
                "FEMEAS_VIVAS" => (int)$total_femeas_vivas,
                "MACHOS_MORTOS" => (int)$total_machos_mortos,
                "FEMEAS_MORTAS" => (int)$total_femeas_mortas,
                "TOTAL_VIVOS" => (int)$total_machos_vivos + (int)$total_femeas_vivas,
                "TOTAL_MORTOS" => (int)$total_machos_mortos + (int)$total_femeas_mortas,
                "ESTACAO_MONTA" => get_estacao_monta($estacao) 
            ];
            array_push($dados);
            return sucesso("Total geral de nascimentos: $total_geral_nascimentos", ["dados"=> $dados, "resumo"=> $somatorio, "estacao"=> $estacao]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function listar_programacao_monta(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario     =  @$params['id_proprietario'];
        $palavra_chave       =  @$params['palavra_chave'];
        $estacao             =  @$params['estacao'];
        $central             =  @$params['central'];
        $metodo_reprodutivo  =  @$params['metodo_reprodutivo'];
   

        if (!@$id_proprietario || !@$metodo_reprodutivo || !isset($estacao)) 
        return erro("Parâmetros inválidos ou faltantes!");
        
        try {
            $estacao = $estacao == "" ? 0 : $estacao;
             // Não permite que a estação seja menor que 0
            $estacao = $estacao == 0 ? estacao_monta(date('Y-m-d')) - 1 : $estacao;
            $filtro_data_cobertura =  $estacao == 0 ? " '1900-01-01' AND '2200-12-31'" : " " . intevalo_datas_estacoes_monta($estacao);
            // Define a Estação de Monta
            $filtro_estacao = " tab_cobricoes.data_cobertura BETWEEN " . intevalo_datas_estacoes_monta($estacao) . " AND ";
            // Não permite que a estação seja menor que 1
            $estacao = $estacao < 1 ? 1 : $estacao;

            // Define a Estação de Monta
            $filtro_estacao = " tab_programa_monta.id_estacao_monta = '$estacao' AND ";
            
            
            // Define a Situação do Produto
            $filtro_metodo = (int)$metodo_reprodutivo == 1 ? "" : "" ;
            $filtro_metodo = (int)$metodo_reprodutivo == 2 ? " tab_programa_monta.id_situacao_emprenhar = '78' AND " : $filtro_metodo;
            $filtro_metodo = (int)$metodo_reprodutivo == 3 ? " tab_programa_monta.id_situacao_emprenhar = '79' AND " : $filtro_metodo;
            
            $query_sql = 
                        "SELECT *,
                        (
                            PREVISTO_01_PROGRAMA +
                            PREVISTO_02_PROGRAMA +
                            PREVISTO_03_PROGRAMA +
                            PREVISTO_04_PROGRAMA +
                            PREVISTO_05_PROGRAMA +
                            PREVISTO_06_PROGRAMA +
                            PREVISTO_07_PROGRAMA +
                            PREVISTO_08_PROGRAMA +
                            PREVISTO_09_PROGRAMA +
                            PREVISTO_10_PROGRAMA                    
                        ) as TOTAL_PREVISTO_PROGRAMA,
                        (
                            CONFIRMADOS_01_PROGRAMA +
                            CONFIRMADOS_02_PROGRAMA +
                            CONFIRMADOS_03_PROGRAMA +
                            CONFIRMADOS_04_PROGRAMA +
                            CONFIRMADOS_05_PROGRAMA +
                            CONFIRMADOS_06_PROGRAMA +
                            CONFIRMADOS_07_PROGRAMA +
                            CONFIRMADOS_08_PROGRAMA +
                            CONFIRMADOS_09_PROGRAMA +
                            CONFIRMADOS_10_PROGRAMA
                        ) as TOTAL_CONFIRMADO_PROGRAMA
                        FROM
                    ( 
                        SELECT  
                        tab_programa_monta.id_cruzamento_previsto AS ID_PROGRAMA,  
                        tab_programa_monta.id_estacao_monta AS ID_ESTACAO_PROGRAMA,  
                        tab_estacao_monta.estacao AS ESTACAO_PROGRAMA, 
                        tab_doadora_matriz.id_animal AS ID_DOADORA_MATRIZ_PROGRAMA,  
                        tab_doadora_matriz.nome AS NOME_DOADORA_MATRIZ_PROGRAMA,
                        tab_central.nome_razao_social AS NOME_CENTRAL_PROGRAMA,  
                        UPPER(tab_situacao_emprenhar.descricao) AS SITUACAO_EMPRENHAR_PROGRAMA,        
                        UPPER(tab_prioridades.descricao) AS PRIORIDADE_EMBRIAO_PROGRAMA,   
                        tab_garanhao1.nome AS NOME_GARANHAO_01_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_01 AS PREVISTO_01_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao   
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao1.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'
                                AND tab_central.nome_razao_social LIKE '%$central%' 
                        ) AS CONFIRMADOS_01_PROGRAMA,  
                        tab_garanhao2.nome AS NOME_GARANHAO_02_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_02 AS PREVISTO_02_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao2.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19' AND NOT tab_toques.id_situacao_prenhez IS NULL  
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea  
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%' 
                        ) AS CONFIRMADOS_02_PROGRAMA,  
                        tab_garanhao3.nome AS NOME_GARANHAO_03_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_03 AS PREVISTO_03_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao3.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'  
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%'
                        ) AS CONFIRMADOS_03_PROGRAMA,   
                        tab_garanhao4.nome AS NOME_GARANHAO_04_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_04 AS PREVISTO_04_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao 
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao4.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%' 
                        ) AS CONFIRMADOS_04_PROGRAMA,   
                        tab_garanhao5.nome AS NOME_GARANHAO_05_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_05 AS PREVISTO_05_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao5.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%'
                        ) AS CONFIRMADOS_05_PROGRAMA,   
                        tab_garanhao6.nome AS NOME_GARANHAO_06_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_06 AS PREVISTO_06_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao   
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao6.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%'
                        ) AS CONFIRMADOS_06_PROGRAMA,   
                        tab_garanhao7.nome AS NOME_GARANHAO_07_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_07 AS PREVISTO_07_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao7.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%' 
                        ) AS CONFIRMADOS_07_PROGRAMA,   
                        tab_garanhao8.nome AS NOME_GARANHAO_08_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_08 AS PREVISTO_08_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao8.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%' 
                        ) AS CONFIRMADOS_08_PROGRAMA,   
                        tab_garanhao9.nome AS NOME_GARANHAO_09_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_09 AS PREVISTO_09_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao9.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%'
                        ) AS CONFIRMADOS_09_PROGRAMA,    
                        tab_garanhao10.nome AS NOME_GARANHAO_10_PROGRAMA,  
                        tab_programa_monta.previsto_garanhao_10 AS PREVISTO_10_PROGRAMA,  
                        (  
                            SELECT  
                                COUNT(tab_cobricoes.id_cobricao)   
                            FROM tab_cobricoes 
                                JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_cobricoes.id_central_reproducao  
                                LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao   
                            WHERE  
                                tab_cobricoes.id_animal_macho = tab_garanhao10.id_animal   
                                AND tab_cobricoes.data_cobertura BETWEEN $filtro_data_cobertura
                                AND tab_toques.id_situacao_prenhez = '19'   
                                AND tab_cobricoes.id_animal_femea = tab_programa_monta.id_animal_femea   
                                AND tab_cobricoes.id_disponibilidade = '76'  
                                AND tab_central.nome_razao_social LIKE '%$central%'
                        ) AS CONFIRMADOS_10_PROGRAMA,
                        (  
                            tab_programa_monta.previsto_garanhao_01 + 
                            tab_programa_monta.previsto_garanhao_02 + 
                            tab_programa_monta.previsto_garanhao_03 +  
                            tab_programa_monta.previsto_garanhao_04 + 
                            tab_programa_monta.previsto_garanhao_05 + 
                            tab_programa_monta.previsto_garanhao_06 + 
                            tab_programa_monta.previsto_garanhao_07 + 
                            tab_programa_monta.previsto_garanhao_08 + 
                            tab_programa_monta.previsto_garanhao_09 + 
                            tab_programa_monta.previsto_garanhao_10  
                        ) AS TOTAL_EMBRIOES_PREVISTOS,
                        tab_programa_monta.informacoes_diversas AS INFORMACAO_PROGRAMA   
                    FROM tab_programa_monta   
                        JOIN tab_pessoas AS tab_central ON tab_central.id_pessoa = tab_programa_monta.id_central_reproducao   
                        JOIN tab_situacoes AS tab_prioridades ON tab_prioridades.id_situacao = tab_programa_monta.id_prioridade  
                        JOIN tab_situacoes AS tab_situacao_emprenhar ON tab_situacao_emprenhar.id_situacao = tab_programa_monta.id_situacao_emprenhar 
                        JOIN tab_animais AS tab_doadora_matriz ON tab_doadora_matriz.id_animal = tab_programa_monta.id_animal_femea
                        JOIN tab_estacao_monta ON tab_estacao_monta.id_estacao_monta = tab_programa_monta.id_estacao_monta 
                        LEFT JOIN tab_animais AS tab_garanhao1 ON tab_garanhao1.id_animal = tab_programa_monta.id_garanhao_01   
                        LEFT JOIN tab_animais AS tab_garanhao2 ON tab_garanhao2.id_animal = tab_programa_monta.id_garanhao_02   
                        LEFT JOIN tab_animais AS tab_garanhao3 ON tab_garanhao3.id_animal = tab_programa_monta.id_garanhao_03   
                        LEFT JOIN tab_animais AS tab_garanhao4 ON tab_garanhao4.id_animal = tab_programa_monta.id_garanhao_04  
                        LEFT JOIN tab_animais AS tab_garanhao5 ON tab_garanhao5.id_animal = tab_programa_monta.id_garanhao_05  
                        LEFT JOIN tab_animais AS tab_garanhao6 ON tab_garanhao6.id_animal = tab_programa_monta.id_garanhao_06   
                        LEFT JOIN tab_animais AS tab_garanhao7 ON tab_garanhao7.id_animal = tab_programa_monta.id_garanhao_07   
                        LEFT JOIN tab_animais AS tab_garanhao8 ON tab_garanhao8.id_animal = tab_programa_monta.id_garanhao_08   
                        LEFT JOIN tab_animais AS tab_garanhao9 ON tab_garanhao9.id_animal = tab_programa_monta.id_garanhao_09   
                        LEFT JOIN tab_animais AS tab_garanhao10 ON tab_garanhao10.id_animal = tab_programa_monta.id_garanhao_10
                    WHERE  
                        $filtro_estacao 
                        $filtro_metodo 
                        tab_programa_monta.id_usuario_sistema = '$id_proprietario' AND   
                        (  
                            tab_doadora_matriz.nome LIKE '%$palavra_chave%' OR  
                            tab_programa_monta.informacoes_diversas LIKE '%$palavra_chave%'  
                        ) AND
                        (
                            tab_central.nome_razao_social LIKE '%$central%'
                        )  
                    GROUP BY tab_programa_monta.id_cruzamento_previsto   
                    ORDER BY tab_doadora_matriz.nome ASC
                ) as tab_programacao_monta_app";

            $pdo = $this->conn->conectar();
            $res = $pdo->query($query_sql);
            
            if(!$res) {
                return erro("Erro: {$pdo->errno} - {$pdo->error}", 500);
            }
            if( !$res->execute() ) {
                return erro("Erro - Código #". $res->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $res->rowCount() <= 0 ) {
                return sucesso("Nenhuma Programação de Monta foi localizada!");
            }
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);    
            
            $embrioes_previstos = 0;
            $embrioes_confirmados = 0;
            foreach ($dados as $key => $value) {
                
                // Soma os Embriões Previstos
                $embrioes_previstos += 
                $value['PREVISTO_01_PROGRAMA'] +
                $value['PREVISTO_02_PROGRAMA'] +
                $value['PREVISTO_03_PROGRAMA'] +
                $value['PREVISTO_04_PROGRAMA'] +
                $value['PREVISTO_05_PROGRAMA'] +
                $value['PREVISTO_06_PROGRAMA'] +
                $value['PREVISTO_07_PROGRAMA'] +
                $value['PREVISTO_08_PROGRAMA'] +
                $value['PREVISTO_09_PROGRAMA'] +
                $value['PREVISTO_10_PROGRAMA'];

                // Soma os Embriões Confirmados
                $embrioes_confirmados +=
                $value['CONFIRMADOS_01_PROGRAMA'] +
                $value['CONFIRMADOS_02_PROGRAMA'] +
                $value['CONFIRMADOS_03_PROGRAMA'] +
                $value['CONFIRMADOS_04_PROGRAMA'] +
                $value['CONFIRMADOS_05_PROGRAMA'] +
                $value['CONFIRMADOS_06_PROGRAMA'] +
                $value['CONFIRMADOS_07_PROGRAMA'] +
                $value['CONFIRMADOS_08_PROGRAMA'] +
                $value['CONFIRMADOS_09_PROGRAMA'] +
                $value['CONFIRMADOS_10_PROGRAMA'];

                //Acrescenta contador
                $dados[$key]['CONTADOR'] =  $key+1;
             
            }
            $total_geral_programacoes = (int)$key+1;
             $somatorio = [
                "TOTAL_GERAL_PROGRAMACOES" => (int)$key+1,
                "TOTAL_EMBRIOES_PREVISTOS" => (int)$embrioes_previstos,
                "TOTAL_EMBRIOES_CONFIRMADOS" => (int)$embrioes_confirmados,
                "ESTACAO_MONTA" => get_estacao_monta($estacao) 
            ];
            array_push($dados);
            return sucesso("Total Geral de Programações: $total_geral_programacoes", ["dados"=> $dados, "resumo"=> $somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
}