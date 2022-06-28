<?php
use Psr\Http\Message\ServerRequestInterface;
class AnimaisModel
{
    private $conn;

    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }
     /**
     * Método index
     * @author Iago Oliveira <iagooliveira09@outlook.com>
     * @return 
     */
    public function index()
    {  
        $pdo = $this->conn->conectar();
        $res = $pdo->query("SELECT COUNT(*) from tab_animais");

        $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
        return json_encode(["mensagem" => "Hello confianca", "Conexão com o BD: " => $retorno]);
    }

    public function detalhes_animal_cobricoes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];
        $id_proprietario = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return json_encode(["codigo" => "2","status" => false, "message" => "Animal ou Proprietário com identificação incorreta!", "data" => ""]);
        try {
            $query_sql = 
                        "SELECT  
                            tab_cobricoes.id_cobricao as ID_COBRICAO, 
                            DATE_FORMAT(tab_cobricoes.data_cobertura, '%d/%m/%Y') as DATA_COBRICAO, 
                            CONCAT(tab_garanhao.nome, ' x ', tab_doadora.nome) as GARANHAO_DOADORA_COBRICAO, 
                            IF(tab_cobricoes.id_te = '18', DATE_FORMAT(tab_cobricoes.data_te, '%d/%m/%Y'), '-') as DATA_TE_COBRICAO, 
                            IF(ISNULL(tab_receptora.nome),'-',CONCAT(tab_receptora.marca, ' - ', tab_receptora.nome)) as NOME_RECEPTORA_COBRICAO, 
                            (
                                CASE 
                                    WHEN tab_toques.id_situacao_prenhez IS NULL OR tab_toques.id_situacao_prenhez = '21' THEN '1' -- Sem Toque
                                    WHEN tab_toques.id_situacao_prenhez = '19' THEN '2' -- Positivo
                                    WHEN tab_toques.id_situacao_prenhez = '20' THEN '3' -- Negativo
                                END
                            ) as ID_TIPO_TOQUE_COBRICAO, 
                            (CASE WHEN tab_toques.id_situacao_prenhez IS NULL THEN 'SEM TOQUE' ELSE CONCAT(UPPER(tab_situacao_toque.descricao),' ',DATE_FORMAT(tab_toques.data_toque,'%d/%m/%Y')) END) as TOQUE_COBRICAO, 
                            IF((tab_toques.id_situacao_prenhez = '19' AND ISNULL(tab_nascimentos.id_nascimento)),CONCAT(DATEDIFF(CURDATE(), tab_cobricoes.data_cobertura), ' Dia(s)'),CONCAT('Nasceu: ',UPPER(tab_nascimentos.nome),' ',IF(tab_nascimentos.id_sexo = '2','(M)','(F)'))) as DIAS_GESTACAO_COBRICAO,
                            tab_central.nome_razao_social as NOME_CENTRAL_COBRICAO, 
                            UPPER(tab_tipos_cobricoes.descricao) as TIPO_COBRICAO, 
                            UPPER(tab_situacoes.descricao) as SITUACAO_COBRICAO, 
                            IF(ISNULL(tab_situacao_sexagens.descricao),'SEM SEXAGEM',UPPER(tab_situacao_sexagens.descricao)) as SEXAGEM_COBRICAO, 
                            IF(ISNULL(tab_comunicacoes_cobricao_associacao.protocolo_comunicacao),'SEM COMUNICAÇÃO',CONCAT(tab_comunicacoes_cobricao_associacao.protocolo_comunicacao,IF((NOT tab_comunicacoes_cobricao_associacao.id_receptora_comunicacao = tab_cobricoes.id_animal_receptora AND NOT tab_comunicacoes_cobricao_associacao.id_receptora_comunicacao IS NULL),CONCAT(' - ', UPPER(tab_receptora_comunicacao.nome)),''))) as PROTOCOLO_COMUNICACAO_COBRICAO  
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
                            (tab_cobricoes.id_animal_macho = '$id_animal' OR tab_cobricoes.id_animal_femea = '$id_animal' OR tab_cobricoes.id_animal_receptora = '$id_animal') AND
                            tab_cobricoes.id_usuario_sistema = '$id_proprietario' AND
                            tab_cobricoes.id_situacao = '1' AND
                            tab_cobricoes.id_disponibilidade = '76'  
                        GROUP BY tab_cobricoes.id_cobricao
                        ORDER BY tab_cobricoes.data_cobertura ASC
                    ";

                $pdo = $this->conn->conectar();
                $res = $pdo->query($query_sql);
                $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum animal foi localizado!", "data" => ""]);
                $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
                return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_exames(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];
        $id_proprietario = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return json_encode(["codigo" => "2","status" => false, "message" => "Animal ou Proprietário com identificação incorreta!", "data" => ""]);
        try {
            $query_sql = 
                        "SELECT  
                        tab_exames.id_exame as ID_EXAME,
                        UPPER(tab_tipos_exames.nome_exame) as TIPO_EXAME,
                        IF(ISNULL(tab_laboratorio.nome_razao_social),'NÃO INFORMADO',CONCAT(UPPER(tab_laboratorio.nome_razao_social),'\nTelefone: ',IF(ISNULL(tab_laboratorio.telefone_celular) OR TRIM(tab_laboratorio.telefone_celular) = '','SEM NÚMERO',tab_laboratorio.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_laboratorio.email_usuario) OR TRIM(tab_laboratorio.email_usuario) = '','SEM E-MAIL',tab_laboratorio.email_usuario))) as LABORATORIO_EXAME,
                        UPPER(tab_resultados.descricao) as RESULTADO_EXAME,
                        DATE_FORMAT(tab_exames.data_resultado, '%d/%m/%Y') as DATA_RESULTADO_EXAME, 
                        DATE_FORMAT(tab_exames.data_validade, '%d/%m/%Y') as DATA_VALIDADE_EXAME,
                        UPPER(tab_situacoes.descricao) as SITUACAO_EXAME
                    FROM tab_exames 
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_exames.id_situacao
                        JOIN tab_situacoes as tab_resultados ON tab_resultados.id_situacao = tab_exames.id_resultado
                        JOIN tab_tipos_exames ON tab_tipos_exames.id_tipo_exame = tab_exames.id_tipo_exame
                        JOIN tab_pessoas as tab_laboratorio ON tab_laboratorio.id_pessoa = tab_exames.id_laboratorio   
                    WHERE 
                        tab_exames.id_animal = '$id_animal' AND tab_exames.id_usuario_sistema = '$id_proprietario' AND
                        tab_exames.id_situacao = '1'
                    GROUP BY tab_exames.id_exame 
                    ORDER BY tab_exames.data_resultado ASC";

                $pdo = $this->conn->conectar();
                $res = $pdo->query($query_sql);
                $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum animal foi localizado!", "data" => ""]);
                $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
                return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_filhos(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];
        $id_proprietario = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return json_encode(["codigo" => "2","status" => false, "message" => "Animal ou Proprietário com identificação incorreta!", "data" => ""]);
        try {
            $query_sql = 
                        "SELECT  
                        tab_exames.id_exame as ID_EXAME,
                        UPPER(tab_tipos_exames.nome_exame) as TIPO_EXAME,
                        IF(ISNULL(tab_laboratorio.nome_razao_social),'NÃO INFORMADO',CONCAT(UPPER(tab_laboratorio.nome_razao_social),'\nTelefone: ',IF(ISNULL(tab_laboratorio.telefone_celular) OR TRIM(tab_laboratorio.telefone_celular) = '','SEM NÚMERO',tab_laboratorio.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_laboratorio.email_usuario) OR TRIM(tab_laboratorio.email_usuario) = '','SEM E-MAIL',tab_laboratorio.email_usuario))) as LABORATORIO_EXAME,
                        UPPER(tab_resultados.descricao) as RESULTADO_EXAME,
                        DATE_FORMAT(tab_exames.data_resultado, '%d/%m/%Y') as DATA_RESULTADO_EXAME, 
                        DATE_FORMAT(tab_exames.data_validade, '%d/%m/%Y') as DATA_VALIDADE_EXAME,
                        UPPER(tab_situacoes.descricao) as SITUACAO_EXAME
                    FROM tab_exames 
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_exames.id_situacao
                        JOIN tab_situacoes as tab_resultados ON tab_resultados.id_situacao = tab_exames.id_resultado
                        JOIN tab_tipos_exames ON tab_tipos_exames.id_tipo_exame = tab_exames.id_tipo_exame
                        JOIN tab_pessoas as tab_laboratorio ON tab_laboratorio.id_pessoa = tab_exames.id_laboratorio   
                    WHERE 
                        tab_exames.id_animal = '$id_animal' AND tab_exames.id_usuario_sistema = '$id_proprietario' AND
                        tab_exames.id_situacao = '1'
                    GROUP BY tab_exames.id_exame 
                    ORDER BY tab_exames.data_resultado ASC";

                $pdo = $this->conn->conectar();
                $res = $pdo->query($query_sql);
                $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum animal foi localizado!", "data" => ""]);
                $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
                return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_genealogia(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];

        if (!$id_animal) return json_encode(["codigo" => "2","status" => false, "message" => "Animal ou Proprietário com identificação incorreta!", "data" => ""]);
        try {
            $query_sql = 
                        "SELECT 
                        tab_animais.id_animal as ID_ANIMAL,
                        IF(ISNULL(tab_pai.nome) OR TRIM(tab_pai.nome) = '','NÃO INFORMADO',tab_pai.nome) as ANIMAL_1,
                        IF(ISNULL(tab_genealogias.animal_11) OR TRIM(tab_genealogias.animal_11) = '','NÃO INFORMADO',tab_genealogias.animal_11) as ANIMAL_11,  
                        IF(ISNULL(tab_genealogias.animal_111) OR TRIM(tab_genealogias.animal_111) = '','NÃO INFORMADO',tab_genealogias.animal_111) as ANIMAL_111,  
                        IF(ISNULL(tab_genealogias.animal_112) OR TRIM(tab_genealogias.animal_112) = '','NÃO INFORMADO',tab_genealogias.animal_112) as ANIMAL_112,  
                        IF(ISNULL(tab_genealogias.animal_12) OR TRIM(tab_genealogias.animal_12) = '','NÃO INFORMADO',tab_genealogias.animal_12) as ANIMAL_12,  
                        IF(ISNULL(tab_genealogias.animal_121) OR TRIM(tab_genealogias.animal_121) = '','NÃO INFORMADO',tab_genealogias.animal_121) as ANIMAL_121,  
                        IF(ISNULL(tab_genealogias.animal_122) OR TRIM(tab_genealogias.animal_122) = '','NÃO INFORMADO',tab_genealogias.animal_122) as ANIMAL_122,
                        IF(ISNULL(tab_mae.nome) OR TRIM(tab_mae.nome) = '','NÃO INFORMADO',tab_mae.nome) as ANIMAL_2,  
                        IF(ISNULL(tab_genealogias.animal_21) OR TRIM(tab_genealogias.animal_21) = '','NÃO INFORMADO',tab_genealogias.animal_21) as ANIMAL_21,  
                        IF(ISNULL(tab_genealogias.animal_211) OR TRIM(tab_genealogias.animal_211) = '','NÃO INFORMADO',tab_genealogias.animal_211) as ANIMAL_211,  
                        IF(ISNULL(tab_genealogias.animal_212) OR TRIM(tab_genealogias.animal_212) = '','NÃO INFORMADO',tab_genealogias.animal_212) as ANIMAL_212,  
                        IF(ISNULL(tab_genealogias.animal_22) OR TRIM(tab_genealogias.animal_22) = '','NÃO INFORMADO',tab_genealogias.animal_22) as ANIMAL_22,  
                        IF(ISNULL(tab_genealogias.animal_221) OR TRIM(tab_genealogias.animal_221) = '','NÃO INFORMADO',tab_genealogias.animal_221) as ANIMAL_221,  
                        IF(ISNULL(tab_genealogias.animal_222) OR TRIM(tab_genealogias.animal_222) = '','NÃO INFORMADO',tab_genealogias.animal_222) as ANIMAL_222
                  FROM tab_animais
                      LEFT JOIN  tab_genealogias ON tab_genealogias.id_animal = tab_animais.id_animal
                    LEFT JOIN tab_animais AS tab_pai ON tab_pai.id_animal = tab_animais.id_pai
                    LEFT JOIN tab_animais AS tab_mae ON tab_mae.id_animal = tab_animais.id_mae
                  WHERE tab_animais.id_animal = '$id_animal'";

                $pdo = $this->conn->conectar();
                $res = $pdo->query($query_sql);
                $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum animal foi localizado!", "data" => ""]);
                $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
                return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_manejo(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return json_encode(["codigo" => false, "status" => false, "message" => "Animal ou Proprietário com identificação incorreta!", "data" => ""]);
        try {
            $query_sql = 
                        "SELECT  
                        tab_entradas_saidas_animais.id_entrada_saida_animal as ID_MOVIMENTACAO,
                        DATE_FORMAT(tab_entradas_saidas_animais.data_movimento, '%d/%m/%Y') as DATA_SANITARIO,
                        UPPER(tab_local_origem.descricao) as LOCAL_ORIGEM_MOVIMENTACAO,
                        IF(ISNULL(tab_pessoa_origem.nome_razao_social),'NÃO INFORMADO',tab_pessoa_origem.nome_razao_social) as PESSOA_ORIGEM_MOVIMENTACAO,
                        tab_local_destino.descricao as LOCAL_DESTINO_MOVIMENTACAO,
                        IF(ISNULL(tab_pessoa_destino.nome_razao_social),'NÃO INFORMADO',tab_pessoa_destino.nome_razao_social) as PESSOA_DESTINO_MOVIMENTACAO, 
                        IF(ISNULL(tab_transportador.nome_razao_social),'NÃO INFORMADO',CONCAT(UPPER(tab_transportador.nome_razao_social),'\nTelefone: ',IF(ISNULL(tab_transportador.telefone_celular) OR TRIM(tab_transportador.telefone_celular) = '','SEM NÚMERO',tab_transportador.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_transportador.email_usuario) OR TRIM(tab_transportador.email_usuario) = '','SEM E-MAIL',tab_transportador.email_usuario))) as TRANSPORTADOR_MOVIMENTACAO,
                        UPPER(tab_situacoes.descricao) as TIPO_MOVIMENTACAO
                    FROM tab_entradas_saidas_animais 
                        JOIN tab_animais_movimentacoes ON tab_animais_movimentacoes.id_entrada_saida_animais = tab_entradas_saidas_animais.id_entrada_saida_animal
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_entradas_saidas_animais.id_situacao_movimento
                        JOIN tab_localizacoes as tab_local_origem ON tab_local_origem.id_localizacao = tab_entradas_saidas_animais.id_local_origem
                        JOIN tab_localizacoes as tab_local_destino ON tab_local_destino.id_localizacao = tab_entradas_saidas_animais.id_local_destino
                        LEFT JOIN tab_pessoas as tab_pessoa_origem ON tab_pessoa_origem.id_pessoa = tab_entradas_saidas_animais.id_pessoa_remetente   
                        LEFT JOIN tab_pessoas as tab_pessoa_destino ON tab_pessoa_destino.id_pessoa = tab_entradas_saidas_animais.id_pessoa_receptor 
                        LEFT JOIN tab_pessoas as tab_transportador ON tab_transportador.id_pessoa = tab_entradas_saidas_animais.id_pessoa_transportador
                    WHERE 
                      tab_animais_movimentacoes.id_animal = '$id_animal' AND tab_entradas_saidas_animais.id_usuario_sistema = '$id_proprietario'
                    GROUP BY tab_entradas_saidas_animais.id_entrada_saida_animal 
                    ORDER BY tab_entradas_saidas_animais.data_movimento ASC";

                $pdo = $this->conn->conectar();
                $res = $pdo->query($query_sql);
                $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum animal foi localizado!", "data" => ""]);
                $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
                return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_negocios(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return json_encode(["codigo" => false, "status" => false, "message" => "Animal ou Proprietário com identificação incorreta!", "data" => ""]);
        try {
            $query_sql = 
                        "SELECT  
                        tab_compras_vendas_animais.id_compra_venda_animal as ID_NEGOCIO, 
                        UPPER(tab_tipo_negocio.descricao) as TIPO_NEGOCIO, 
                        DATE_FORMAT(tab_compras_vendas_animais.data_compra_venda, '%d/%m/%Y') as DATA_NEGOCIO,
                        UPPER(tab_tipos_produtos_negocios.descricao_produto) as TIPO_PRODUTO_NEGOCIO, 
                        IF(tab_compras_vendas_animais.id_tipo_produto = '4', tab_compras_vendas_animais.id_produto_embriao,tab_compras_vendas_animais.id_produto_animal) as ID_PRODUTO_NEGOCIO,
                        IF( 
                            tab_compras_vendas_animais.id_tipo_produto = '4', 
                            CONCAT( 
                            'Garanhão: ', UPPER(tab_garanhao.nome), 
                            '\nDoadora/Matriz: ', UPPER(tab_doadora.nome), 
                            '\nData de Cobrição: ', IF(tab_cobricoes.id_disponibilidade = '76', CONCAT(DATE_FORMAT(tab_cobricoes.data_cobertura, '%d/%m/%Y'),' - Parto Previsto: ',DATE_FORMAT(ADDDATE(tab_cobricoes.data_cobertura, INTERVAL 330 DAY),'%m/%Y')),'A DEFINIR'), 
                            IF( 
                                tab_cobricoes.id_te = '18', 
                                CONCAT('\nReceptora: ',UPPER(tab_receptora.marca), ' - ', UPPER(tab_receptora.nome)), 
                                '' 
                                ), 
                                '\nDisponibilidade: ',UPPER(tab_disponibilidade.descricao),  
                                IF( 
                                    NOT tab_sexagens.id_resultado_sexagem IS NULL, 
                                    CONCAT('\nSexagem: ',UPPER(tab_sexo.descricao)), 
                                    '\nSexagem: NÃO SEXADO' 
                                ) 
                            ), 
                        CONCAT(UPPER(tab_animais.nome),'\nPai: ',IF(ISNULL(tab_pai.nome),'DESCONHECIDO',UPPER(tab_pai.nome)),' X ','Mãe: ',IF(ISNULL(tab_mae.nome),'DESCONHECIDA',UPPER(tab_mae.nome))) 
                        ) as DESCRICAO_PRODUTO_NEGOCIO, 
                        CONCAT(UPPER(tab_pessoas.nome_razao_social),'\nTelefone: ',IF(ISNULL(tab_pessoas.telefone_celular) OR TRIM(tab_pessoas.telefone_celular) = '','SEM NÚMERO',tab_pessoas.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_pessoas.email_usuario) OR TRIM(tab_pessoas.email_usuario) = '','SEM E-MAIL',tab_pessoas.email_usuario)) as NOME_COMPRADOR_VENDEDOR_NEGOCIO,  
                        UPPER(tab_eventos_equestres.nome_evento) as NOME_EVENTO_COMPRA_VENDA, 
                        UPPER(tab_situacao_entrega_recebimento.descricao) as SITUACAO_ENTREGA_RECEBIMENTO_NEGOCIO, 
                        UPPER(tab_situacao_negocio.descricao) as SITUACAO_NEGOCIO, 
                        FORMAT(tab_compras_vendas_animais.quantidade_compra_venda, 2, 'de_DE') as QUANTIDADE_NEGOCIO, 
                        CONCAT(FORMAT(tab_compras_vendas_animais.cotas_compra_venda, 2, 'de_DE'),'%') as COTAS_NEGOCIO, 
                        tab_compras_vendas_animais.valor_total as VALOR_TOTAL_NEGOCIO 
                    FROM tab_compras_vendas_animais  
                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_compras_vendas_animais.id_comprador_vendedor  
                        JOIN tab_tipos_produtos_negocios ON tab_tipos_produtos_negocios.id_produto_negocio = tab_compras_vendas_animais.id_tipo_produto  
                        JOIN tab_eventos_equestres ON tab_eventos_equestres.id_evento_equestre = tab_compras_vendas_animais.id_evento_compra_venda  
                        JOIN tab_situacoes AS tab_tipo_negocio ON tab_tipo_negocio.id_situacao = tab_compras_vendas_animais.id_tipo_negocio
                        JOIN tab_situacoes AS tab_situacao_entrega_recebimento ON tab_situacao_entrega_recebimento.id_situacao = tab_compras_vendas_animais.id_situacao_recebimento_entrega
                        JOIN tab_situacoes AS tab_situacao_negocio ON tab_situacao_negocio.id_situacao = tab_compras_vendas_animais.id_situacao_negocio
                        LEFT JOIN tab_animais ON tab_animais.id_animal = tab_compras_vendas_animais.id_produto_animal  
                        LEFT JOIN tab_cobricoes ON tab_cobricoes.id_cobricao = tab_compras_vendas_animais.id_produto_embriao  
                        LEFT JOIN tab_animais AS tab_pai ON tab_pai.id_animal = tab_animais.id_pai  
                        LEFT JOIN tab_animais AS tab_mae ON tab_mae.id_animal = tab_animais.id_mae 
                        LEFT JOIN tab_animais AS tab_garanhao ON tab_garanhao.id_animal = tab_cobricoes.id_animal_macho  
                        LEFT JOIN tab_animais AS tab_doadora ON tab_doadora.id_animal = tab_cobricoes.id_animal_femea  
                        LEFT JOIN tab_animais AS tab_receptora ON tab_receptora.id_animal = tab_cobricoes.id_animal_receptora  
                        LEFT JOIN tab_toques ON tab_toques.id_cobricao_relacionada = tab_cobricoes.id_cobricao  
                        LEFT JOIN tab_situacoes AS tab_disponibilidade ON tab_disponibilidade.id_situacao = tab_cobricoes.id_disponibilidade  
                        LEFT JOIN tab_sexagens ON tab_sexagens.id_cobricao_relacionada = tab_cobricoes.id_cobricao  
                        LEFT JOIN tab_situacoes AS tab_sexo ON tab_sexo.id_situacao = tab_sexagens.id_resultado_sexagem 
                    WHERE 
                        (tab_compras_vendas_animais.id_produto_animal = :ID_ANIMAL OR tab_garanhao.id_animal = :ID_ANIMAL OR tab_doadora.id_animal = :ID_ANIMAL OR tab_receptora.id_animal = :ID_ANIMAL) AND
                        tab_compras_vendas_animais.id_situacao_negocio = '42' AND 
                        tab_compras_vendas_animais.id_usuario_sistema = :ID_PROPRIETARIO  
                    ORDER BY  
                        tab_compras_vendas_animais.data_compra_venda ASC
            ";

                $pdo = $this->conn->conectar();
                $res = $pdo->prepare($query_sql);

                $res->bindValue(':ID_ANIMAL', $id_animal);
                $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
                $res->execute();            
                $retorno = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($retorno) <= 0) return  $resposta = json_encode(["codigo" => false,"status" => false, "message" => "Nenhum animal foi localizado!", "data" => ""]);
                $resposta = ["codigo" => true, "status" => "sucesso", "message" => "", "data" => $retorno];

                
                return json_encode($resposta);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
}
