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
        return json_encode(["codigo" => false, "status" => false, "message" => "Cobertura ou Proprietário com identificação incorreta!", "data" => ""]);
        
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
            $res->execute();
            $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Cobrição ou Proprietário com identificação incorreta!", "data" => ""]);
            $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
            return json_encode($resposta);
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
        return json_encode(["codigo" => false, "status" => false, "message" => "Nascimento ou Proprietário com identificação incorreta!", "data" => ""]);
        
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
            $res->execute();
            $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nascimento ou Proprietário com identificação incorreta!", "data" => ""]);
            $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
            return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function listar_banco_nomes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        
        $sexo               = @$params['sexo'];
        $letra_alfabeto     = @$params['letra_alfabeto'];

        if (!trim(@$sexo) || !trim(@$letra_alfabeto) ) 
        return json_encode(["codigo" => false, "status" => false, "message" => "Parâmetros inválidos ou faltantes!", "data" => ""]);
        
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
            $res->execute();
            $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum Nome foi localizado!", "data" => ""]);
            
            foreach ($retorno as $key => $value) {
                //Acrescenta contador
                $retorno[$key]['CONTADOR'] =  $key+1;
             }
             $contador = ($key +1);
             $resposta = ["codigo" => true, "status" => "sucesso", "message" => "Foram encontrados '$contador' nomes com a letra '$letra_alfabeto'!", "data" => $retorno];
            return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function listar_centrais(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario = @$params['id_proprietario'];

        if (!@$id_proprietario) 
        return json_encode(["codigo" => false, "status" => false, "message" => "Parâmetros inválidos ou faltantes!", "data" => ""]);
        
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
            $res->execute();

            $retorno = $res->fetchAll(PDO::FETCH_ASSOC);    
            if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhuma Central de Reprodução foi localizada!", "data" => ""]);
            $totalizador = 0;
            foreach ($retorno as $key => $value) {
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
                $retorno[$key]['ESTACOES'] = $dados_estacoes;
                //Acrescenta contador
                $retorno[$key]['CONTADOR'] =  $key+1;
             }
             $somatorio = ["TOTAL_COBRICOES_CENTRAIS" => $totalizador];
             $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno,"RESUMO" => $somatorio];
            return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
}