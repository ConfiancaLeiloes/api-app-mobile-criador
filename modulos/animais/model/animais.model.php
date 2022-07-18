<?php
use Psr\Http\Message\ServerRequestInterface;

//define('URL_FOTOS', "https://www.agrobold.com.br/agrobold_equinos/fotos_animais/");
class AnimaisModel
{
    private $conn;
    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }

    /**
     * PLANTEL
     */
    public function detalhes_animal_cobricoes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];
        $id_proprietario = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!");

        try {
            $query_sql = 
            "   SELECT  
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
                    (CASE WHEN tab_toques.id_situacao_prenhez IS NULL THEN 'SEM TOQUE' ELSE UPPER(tab_situacao_toque.descricao) END) as TOQUE_COBRICAO, 
                    (CASE WHEN tab_toques.id_situacao_prenhez IS NULL THEN '00/00/0000' ELSE DATE_FORMAT(tab_toques.data_toque,'%d/%m/%Y') END) as DATA_TOQUE_COBRICAO, 
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
                WHERE  (
                    (
                        tab_cobricoes.id_animal_macho = '{$id_animal}' OR
                        tab_cobricoes.id_animal_femea = '{$id_animal}' OR 
                        tab_cobricoes.id_animal_receptora = '{$id_animal}'
                    )
                    AND tab_cobricoes.id_usuario_sistema = '{$id_proprietario}'
                    AND tab_cobricoes.id_disponibilidade = '76'  
                    AND tab_cobricoes.id_situacao = '1'
                )
                GROUP BY tab_cobricoes.id_cobricao
                ORDER BY tab_cobricoes.data_cobertura ASC
            ";

            $connect = $this->conn->conectar();

            $stmt = $connect->prepare($query_sql);
            if(!$stmt) {
                return erro("Erro: {$connect->errno} - {$connect->error}", 500);
            }
            if( !$stmt->execute() ) {
                return erro("Erro - Código #". $stmt->errorInfo()[modo_dev() ? 2 : 1], 500);
            }
            if ( $stmt->rowCount() <= 0 ) {
                return sucesso("Nenhum animal encontrado!");
            }
            
            return sucesso("{$stmt->rowCount()} resultado(s) encontrado(s)",["dados"=>$stmt->fetchAll(PDO::FETCH_OBJ)]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage, (int)$th->getCode);
        }
        
    }
    public function detalhes_animal_exames(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];
        $id_proprietario = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Haras com identificação incorreta!", []);
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
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return sucesso("Nenhum Exame foi localizado!");
                
                return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_filhos(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];
        $id_proprietario = $params['id_proprietario'];
        $url_fotos = URL_FOTOS;

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!");
        try {
            $query_sql = 
                        "SELECT  
                        tab_animais.id_animal as ID_ANIMAL, 
                        UPPER(tab_grupo_animais.descricao) as GRUPO_ANIMAL, 
                        tab_animais.nome as NOME_ANIMAL, 
                        UPPER(tab_animais.marca) as MARCA_ANIMAL, 
                        UPPER(tab_sexos.sexo_animal) as SEXO_ANIMAL, 
                        DATE_FORMAT(tab_animais.data_nascimento, '%d/%m/%Y') as NASCIMENTO_ANIMAL, 
                        tab_pai_animal.nome as PAI_ANIMAL, 
                        tab_mae_animal.nome as MAE_ANIMAL, 
                        tab_animais.registro_associacao as REGISTRO_ANIMAL, 
                        UPPER(tab_situacoes.descricao) as DESCRICAO_SITUACAO_ANIMAL,  
                        IF(ISNULL(tab_socios.cotas_socio_01),'0.00',tab_socios.cotas_socio_01) as COTAS_ANIMAL,
                        IF(tab_animais.foto_perfil_animal = 'sem_foto.jpg',null,CONCAT('$url_fotos',tab_animais.foto_perfil_animal)) as FOTO_ANIMAL 
                    FROM tab_animais  
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao   
                        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo   
                        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo  
                        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai  
                        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae   
                        LEFT JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal   
                    WHERE 
                        (tab_animais.id_pai = :ID_ANIMAL OR tab_animais.id_mae = :ID_ANIMAL) AND
                        tab_animais.id_usuario_sistema = :ID_PROPRIETARIO AND
                        tab_animais.id_situacao_cadastro = '11' AND
                        tab_animais.id_situacao = '1' AND
                        tab_animais.id_situacao_vida = '15'
                    GROUP BY tab_animais.id_animal  
                    ORDER BY tab_animais.nome ASC";

                $pdo = $this->conn->conectar();
                $res = $pdo->prepare($query_sql);

                $res->bindValue(':ID_ANIMAL', $id_animal);
                $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);

                $res->execute();
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return sucesso("Nenhum animal foi localizado!");
                
                $total_machos = 0;
                $total_femeas = 0;
                foreach ($dados as $key => $value) {
                    // Soma os Animais
                trim($value['SEXO_ANIMAL']) == "MACHO" ? $total_machos++ : $total_femeas++;

                //Acrescenta contador
                $dados[$key]['CONTADOR'] =  $key+1;
                }

                // Monta o Array do Somatório
                $somatorio = [
                    "TOTAL_GERAL_FILHOS" => (int)$key+1,
                    "TOTAL_MACHOS" => (int)$total_machos,
                    "TOTAL_FEMEAS" => (int)$total_femeas
                ];

                return sucesso("", ["dados"=>$dados, "resumo"=>$somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_genealogia(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal = $params['id_animal'];

        if (!$id_animal) return erro("Animal com identificação incorreta!");
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
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return sucesso("Animal com identificação incorreta!");

                return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_manejo(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Haras com identificação incorreta!");
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
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return sucesso("Nenhuma Movimentação foi localizada!");

                return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_negocios(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!");
    
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
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return sucesso("Animal ou Proprietário com identificação incorreta!");
                
                return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_perfil(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];
        $url_fotos          = URL_FOTOS;

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!");
        
        try {
            $query_sql = 
                        "SELECT  
                        tab_animais.id_animal as ID_ANIMAL,  
                        tab_animais.nome as NOME_ANIMAL,  
                        IF(ISNULL(tab_animais.marca) OR TRIM(tab_animais.marca) = '','SEM MARCA',UPPER(tab_animais.marca)) as MARCA_ANIMAL,  
                        DATE_FORMAT(tab_animais.data_nascimento, '%d/%m/%Y') as DATA_NASCIMENTO_ANIMAL,  
                        tab_animais.registro_associacao as REGISTRO_ANIMAL,  
                        tab_animais.chip as CHIP_ANIMAL,
                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_01),'0.00',tab_socios.cotas_socio_01), 2, 'de_DE'),'%') as COTAS_ANIMAL,    
                        UPPER(tab_racas.descricao) as NOME_RACA_ANIMAL,    
                        UPPER(tab_pelagens.descricao) as NOME_PELAGEM_ANIMAL,  
                        tab_pai_animal.id_animal as ID_PAI_ANIMAL,  
                        UPPER(tab_pai_animal.nome) as NOME_PAI_ANIMAL,  
                        tab_mae_animal.id_animal as ID_MAE_ANIMAL,  
                        UPPER(tab_mae_animal.nome) as NOME_MAE_ANIMAL,    
                        UPPER(tab_proprietario.nome_razao_social) as NOME_PROPRIETARIO_ANIMAL,   
                        UPPER(tab_criador.nome_razao_social) as NOME_CRIADOR_ANIMAL, 
                        UPPER(tab_localizacoes.descricao) as LOCALIZACAO_ANIMAL,    
                        UPPER(tab_classificacoes.nota_classificacao) as NOTA_CLASSIFICACAO_ANIMAL,  
                        IF(ISNULL(tab_animais.grau_de_sangue) OR TRIM(tab_animais.grau_de_sangue) = '','NÃO INFORMADO',UPPER(tab_animais.grau_de_sangue)) as GRAU_DE_SANGUE_ANIMAL,  
                        UPPER(tab_grupo_animais.descricao) as GRUPO_ANIMAL,  
                        UPPER(tab_sexos.sexo_animal) as SEXO_ANIMAL,  
                        UPPER(tab_dna.descricao) as SITUACAO_DNA,  
                        UPPER(tab_situacao_animal.descricao) as SITUACAO_ANIMAL,
                        UPPER(tab_animais.causa_morte) as CAUSA_MORTE, 
                        UPPER(tab_animais.data_morte) as DATA_MORTE, 
                        IF(tab_animais.id_sexo = '2', UPPER(tab_situacao_castrado.descricao), '-') as SITUACAO_MACHO_CASTRADO_ANIMAL, 
                        UPPER(tab_situacao_vida.descricao) as SITUACAO_VIDA_ANIMAL, 
                        IF(ISNULL(tab_animais.informacoes_diversas) OR TRIM(tab_animais.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_animais.informacoes_diversas) as INFORMACOES_DIVERSAS_ANIMAL,  
                        CONCAT('$url_fotos',tab_animais.foto_perfil_animal) as FOTO_ANIMAL,
                        (
                            SELECT 
                                COUNT(tab_animais.id_animal) 
                            FROM tab_animais
                            WHERE (tab_animais.id_pai = :ID_ANIMAL OR tab_animais.id_mae = :ID_ANIMAL) AND
                                tab_animais.id_situacao_cadastro = '11' AND
                                tab_animais.id_situacao = '1' AND
                                tab_animais.id_situacao_vida = '15'                    
                        ) as TOTAL_FILHOS,
                        (
                            SELECT 
                                COUNT(tab_cobricoes.id_cobricao) 
                            FROM tab_cobricoes
                            WHERE (tab_cobricoes.id_animal_macho = :ID_ANIMAL OR tab_cobricoes.id_animal_femea = :ID_ANIMAL OR tab_cobricoes.id_animal_receptora = :ID_ANIMAL) AND
                                tab_cobricoes.id_situacao = '1' AND
                                tab_cobricoes.id_disponibilidade = '76'
                        ) as TOTAL_COBRICOES,
                        (
                            SELECT 
                                COUNT(tab_compras_vendas_animais.id_compra_venda_animal) 
                            FROM tab_compras_vendas_animais
                                LEFT JOIN tab_cobricoes ON tab_cobricoes.id_cobricao = tab_compras_vendas_animais.id_produto_embriao 
                                LEFT JOIN tab_animais AS tab_garanhao ON tab_garanhao.id_animal = tab_cobricoes.id_animal_macho
                                LEFT JOIN tab_animais AS tab_doadora ON tab_doadora.id_animal = tab_cobricoes.id_animal_femea
                                LEFT JOIN tab_animais AS tab_receptora ON tab_receptora.id_animal = tab_cobricoes.id_animal_receptora
                            WHERE (tab_compras_vendas_animais.id_produto_animal = :ID_ANIMAL OR tab_garanhao.id_animal = :ID_ANIMAL OR tab_doadora.id_animal = :ID_ANIMAL OR tab_receptora.id_animal = :ID_ANIMAL) AND
                                tab_compras_vendas_animais.id_situacao_negocio = '42'
                        ) as TOTAL_NEGOCIOS,
                        (
                            SELECT 
                                COUNT(tab_animais_movimentacoes.id_animais_movimentacoes) 
                            FROM tab_animais_movimentacoes
                            WHERE (tab_animais_movimentacoes.id_animal = :ID_ANIMAL)
                        ) as TOTAL_MANEJOS,
                        (
                            SELECT 
                                COUNT(tab_animais_manejo.id_animal_manejo) 
                            FROM tab_animais_manejo
                            WHERE (tab_animais_manejo.id_animal = :ID_ANIMAL)
                        ) as TOTAL_SANITARIOS,
                        (
                            SELECT 
                                COUNT(tab_exames.id_exame) 
                            FROM tab_exames
                            WHERE tab_exames.id_animal = :ID_ANIMAL AND tab_exames.id_situacao = '1'
                        ) as TOTAL_EXAMES,
                        (
                            SELECT 
                                (
                                   IF(ISNULL(tab_socios.id_socio_02),'0','1') +
                                   IF(ISNULL(tab_socios.id_socio_03),'0','1') +
                                   IF(ISNULL(tab_socios.id_socio_04),'0','1') +
                                   IF(ISNULL(tab_socios.id_socio_05),'0','1') +
                                   IF(ISNULL(tab_socios.id_socio_06),'0','1') +
                                   IF(ISNULL(tab_socios.id_socio_07),'0','1') +
                                   IF(ISNULL(tab_socios.id_socio_08),'0','1')
                                ) 
                            FROM tab_socios
                            WHERE tab_socios.id_animal = :ID_ANIMAL
                        ) as TOTAL_SOCIOS
                      FROM tab_animais  
                        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                        JOIN tab_situacoes AS tab_dna ON tab_dna.id_situacao = tab_animais.id_dna
                        JOIN tab_situacoes AS tab_situacao_animal ON tab_situacao_animal.id_situacao = tab_animais.id_situacao
                        JOIN tab_situacoes AS tab_situacao_castrado ON tab_situacao_castrado.id_situacao = tab_animais.id_situacao_macho_castrado
                        JOIN tab_situacoes AS tab_situacao_vida ON tab_situacao_vida.id_situacao = tab_animais.id_situacao_vida
                        LEFT JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                        LEFT JOIN tab_racas ON tab_racas.id_raca = tab_animais.id_raca  
                        LEFT JOIN tab_pelagens ON tab_pelagens.id_pelagem = tab_animais.id_pelagem  
                        LEFT JOIN tab_classificacoes ON tab_classificacoes.id_classificacao = tab_animais.id_classificacao  
                        LEFT JOIN tab_localizacoes ON tab_localizacoes.id_localizacao = tab_animais.id_localizacao  
                        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai  
                        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae  
                        LEFT JOIN tab_pessoas AS tab_proprietario ON tab_proprietario.id_pessoa = tab_animais.id_proprietario  
                        LEFT JOIN tab_pessoas AS tab_criador ON tab_criador.id_pessoa = tab_animais.id_criador   
                      WHERE tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO
            ";

                $pdo = $this->conn->conectar();
                $res = $pdo->prepare($query_sql);

                $res->bindValue(':ID_ANIMAL', $id_animal);
                $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
                
                $res->execute();            
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return erro("Animal ou Proprietário com identificação incorreta!");
                
                return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_sanitario(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!", []);
        try {
                $query_sql = 
                        "SELECT  
                            tab_controle_sanitario.id_manejo as ID_SANITARIO,  
                            tab_controle_sanitario.descricao as DESCRICAO_SANITARIO, 
                         DATE_FORMAT(tab_controle_sanitario.data_inicio, '%d/%m/%Y') as DATA_SANITARIO, 
                            tab_controle_sanitario.id_situacao as ID_SITUACAO_SANITARIO,
                            UPPER(tab_situacoes.descricao) as SITUACAO_SANITARIO,
                            UPPER(tab_pessoas.nome_razao_social) as RESPONSAVEL_SANITARIO
                         FROM tab_animais_manejo 
                             JOIN tab_controle_sanitario ON tab_controle_sanitario.id_manejo = tab_animais_manejo.id_manejo
                             JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_controle_sanitario.id_situacao
                             JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_controle_sanitario.id_veterinario_colaborador   
                         WHERE 
                                tab_animais_manejo.id_animal = :ID_ANIMAL AND tab_controle_sanitario.id_usuario_sistema = :ID_PROPRIETARIO
                         GROUP BY tab_controle_sanitario.id_manejo 
                         ORDER BY tab_controle_sanitario.data_inicio ASC";

                $pdo = $this->conn->conectar();
                $res = $pdo->prepare($query_sql);

                $res->bindValue(':ID_ANIMAL', $id_animal);
                $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);

                $res->execute();
                $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

                if (count($dados) <= 0) return sucesso("Nenhum Controle Sanitário foi localizado!");

                return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function detalhes_animal_socios(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_animal          = $params['id_animal'];
        $id_proprietario    = $params['id_proprietario'];

        if (!$id_animal || !$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!");
        try {
            $query_sql = 
                        "SELECT * FROM 
                        ( 
                            (	 
                                (	
                                    SELECT 
                                        tab_pessoas.id_pessoa as ID_SOCIO, 
                                        tab_pessoas.nome_razao_social as NOME_SOCIO,  
                                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_02),'0.00',tab_socios.cotas_socio_02), 2, 'de_DE'),'%') as COTAS_SOCIO,  
                                        IF(ISNULL(tab_pessoas.nome_propriedade_fazenda),'-',tab_pessoas.nome_propriedade_fazenda) as FAZENDA_SOCIO,  
                                        IF(ISNULL(tab_cidades.nome_cidade),'-',tab_cidades.nome_cidade) AS CIDADE_SOCIO,  
                                        IF(ISNULL(tab_estados.sigla_estado),'-',tab_estados.sigla_estado) as ESTADO_SOCIO,  
                                        IF(TRIM(CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) = '', '-',CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) as TELEFONE_SOCIO,  
                                        IF(ISNULL(tab_pessoas.email_usuario),'-',tab_pessoas.email_usuario) as EMAIL_SOCIO 
                                    FROM tab_animais  
                                        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_02  
                                        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                                        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf  
                                    WHERE 
                                        tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO
                                    GROUP BY tab_animais.id_animal  
                                    ORDER BY tab_animais.nome ASC  
                                )
                                UNION
                                (	
                                    SELECT 
                                        tab_pessoas.id_pessoa as ID_SOCIO, 
                                        tab_pessoas.nome_razao_social as NOME_SOCIO,  
                                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_03),'0.00',tab_socios.cotas_socio_03), 2, 'de_DE'),'%') as COTAS_SOCIO,  
                                        IF(ISNULL(tab_pessoas.nome_propriedade_fazenda),'-',tab_pessoas.nome_propriedade_fazenda) as FAZENDA_SOCIO,  
                                        IF(ISNULL(tab_cidades.nome_cidade),'-',tab_cidades.nome_cidade) AS CIDADE_SOCIO,  
                                        IF(ISNULL(tab_estados.sigla_estado),'-',tab_estados.sigla_estado) as ESTADO_SOCIO,  
                                        IF(TRIM(CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) = '', '-',CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) as TELEFONE_SOCIO,  
                                        IF(ISNULL(tab_pessoas.email_usuario),'-',tab_pessoas.email_usuario) as EMAIL_SOCIO 
                                    FROM tab_animais  
                                        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_03  
                                        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                                        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf  
                                    WHERE 
                                        tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO 
                                    GROUP BY tab_animais.id_animal  
                                    ORDER BY tab_animais.nome ASC  
                                )
                                UNION
                                (	
                                    SELECT 
                                        tab_pessoas.id_pessoa as ID_SOCIO, 
                                        tab_pessoas.nome_razao_social as NOME_SOCIO,  
                                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_04),'0.00',tab_socios.cotas_socio_04), 2, 'de_DE'),'%') as COTAS_SOCIO,  
                                        IF(ISNULL(tab_pessoas.nome_propriedade_fazenda),'-',tab_pessoas.nome_propriedade_fazenda) as FAZENDA_SOCIO,  
                                        IF(ISNULL(tab_cidades.nome_cidade),'-',tab_cidades.nome_cidade) AS CIDADE_SOCIO,  
                                        IF(ISNULL(tab_estados.sigla_estado),'-',tab_estados.sigla_estado) as ESTADO_SOCIO,  
                                        IF(TRIM(CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) = '', '-',CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) as TELEFONE_SOCIO,  
                                        IF(ISNULL(tab_pessoas.email_usuario),'-',tab_pessoas.email_usuario) as EMAIL_SOCIO 
                                    FROM tab_animais  
                                        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_04  
                                        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                                        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf  
                                    WHERE 
                                        tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO 
                                    GROUP BY tab_animais.id_animal  
                                    ORDER BY tab_animais.nome ASC  
                                )
                                UNION
                                (	
                                    SELECT 
                                        tab_pessoas.id_pessoa as ID_SOCIO, 
                                        tab_pessoas.nome_razao_social as NOME_SOCIO,  
                                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_05),'0.00',tab_socios.cotas_socio_05), 2, 'de_DE'),'%') as COTAS_SOCIO,  
                                        IF(ISNULL(tab_pessoas.nome_propriedade_fazenda),'-',tab_pessoas.nome_propriedade_fazenda) as FAZENDA_SOCIO,  
                                        IF(ISNULL(tab_cidades.nome_cidade),'-',tab_cidades.nome_cidade) AS CIDADE_SOCIO,  
                                        IF(ISNULL(tab_estados.sigla_estado),'-',tab_estados.sigla_estado) as ESTADO_SOCIO,  
                                        IF(TRIM(CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) = '', '-',CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) as TELEFONE_SOCIO,  
                                        IF(ISNULL(tab_pessoas.email_usuario),'-',tab_pessoas.email_usuario) as EMAIL_SOCIO 
                                    FROM tab_animais  
                                        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_05  
                                        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                                        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf  
                                    WHERE 
                                        tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO 
                                    GROUP BY tab_animais.id_animal  
                                    ORDER BY tab_animais.nome ASC  
                                )
                                UNION
                                (	
                                    SELECT 
                                        tab_pessoas.id_pessoa as ID_SOCIO, 
                                        tab_pessoas.nome_razao_social as NOME_SOCIO,  
                                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_06),'0.00',tab_socios.cotas_socio_06), 2, 'de_DE'),'%') as COTAS_SOCIO,  
                                        IF(ISNULL(tab_pessoas.nome_propriedade_fazenda),'-',tab_pessoas.nome_propriedade_fazenda) as FAZENDA_SOCIO,  
                                        IF(ISNULL(tab_cidades.nome_cidade),'-',tab_cidades.nome_cidade) AS CIDADE_SOCIO,  
                                        IF(ISNULL(tab_estados.sigla_estado),'-',tab_estados.sigla_estado) as ESTADO_SOCIO,  
                                        IF(TRIM(CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) = '', '-',CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) as TELEFONE_SOCIO,  
                                        IF(ISNULL(tab_pessoas.email_usuario),'-',tab_pessoas.email_usuario) as EMAIL_SOCIO 
                                    FROM tab_animais  
                                        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_06  
                                        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                                        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf  
                                    WHERE 
                                        tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO 
                                    GROUP BY tab_animais.id_animal  
                                    ORDER BY tab_animais.nome ASC  
                                )
                                UNION
                                (	
                                    SELECT 
                                        tab_pessoas.id_pessoa as ID_SOCIO, 
                                        tab_pessoas.nome_razao_social as NOME_SOCIO,  
                                        CONCAT(FORMAT(IF(ISNULL(tab_socios.cotas_socio_07),'0.00',tab_socios.cotas_socio_07), 2, 'de_DE'),'%') as COTAS_SOCIO,  
                                        IF(ISNULL(tab_pessoas.nome_propriedade_fazenda),'-',tab_pessoas.nome_propriedade_fazenda) as FAZENDA_SOCIO,  
                                        IF(ISNULL(tab_cidades.nome_cidade),'-',tab_cidades.nome_cidade) AS CIDADE_SOCIO,  
                                        IF(ISNULL(tab_estados.sigla_estado),'-',tab_estados.sigla_estado) as ESTADO_SOCIO,  
                                        IF(TRIM(CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) = '', '-',CONCAT(tab_pessoas.telefone_celular, ' ', tab_pessoas.telefone_fixo)) as TELEFONE_SOCIO,  
                                        IF(ISNULL(tab_pessoas.email_usuario),'-',tab_pessoas.email_usuario) as EMAIL_SOCIO 
                                    FROM tab_animais  
                                        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_07  
                                        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                                        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf  
                                    WHERE 
                                        tab_animais.id_animal = :ID_ANIMAL AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO 
                                    GROUP BY tab_animais.id_animal  
                                    ORDER BY tab_animais.nome ASC  
                                )
                                ORDER BY NOME_SOCIO ASC
                            ) as tab_socios_animais_sistema		
                        )  
                        HAVING COTAS_SOCIO < 100 AND COTAS_SOCIO > 0";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);

            $res->bindValue(':ID_ANIMAL', $id_animal);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);

            $res->execute();
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                

            if (count($dados) <= 0) return  sucesso("Nenhum Sócio foi localizado!");

            return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function listar_socios_condominios(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $id_tipo_animal     = $params['id_tipo_animal'];
        $grupo              = $params['grupo'];
        $palavra_chave      = $params['palavra_chave'];
        $id_situacao        = $params['situacao'];

        if (!$id_proprietario) return erro("Animal ou Proprietário com identificação incorreta!");
        
        try {

            // Define o Grupo
            $filtro_grupo = (int)$grupo == 99 ? "" : " AND tab_animais.id_grupo = $grupo  ";

            $query_sql = 
                        "
                        SELECT
                          *
                        FROM
                          (
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_01 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_01
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_02 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_02),
                                  '0.00',
                                  tab_socios.cotas_socio_02
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_02
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_03 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_03),
                                  '0.00',
                                  tab_socios.cotas_socio_03
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_03
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_04 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_04),
                                  '0.00',
                                  tab_socios.cotas_socio_04
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_04
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_05 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_05),
                                  '0.00',
                                  tab_socios.cotas_socio_05
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_05
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_06, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_01 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_06),
                                  '0.00',
                                  tab_socios.cotas_socio_06
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_06
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_07 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_07),
                                  '0.00',
                                  tab_socios.cotas_socio_07
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_07
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            UNION
                            (
                              SELECT
                                tab_animais.id_animal AS ID_ANIMAL,
                                tab_grupo_animais.descricao AS GRUPO_ANIMAL,
                                tab_animais.nome AS NOME_ANIMAL,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_01),
                                  '0.00',
                                  tab_socios.cotas_socio_01
                                ) AS COTAS_ANIMAL,
                                tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
                                tab_pai_animal.nome AS PAI_ANIMAL,
                                tab_mae_animal.nome AS MAE_ANIMAL,
                                tab_animais.registro_associacao AS REGISTRO_ANIMAL,
                                CONCAT(
                                  tab_pessoas.nome_razao_social,
                                  '\nPermanência: ',
                                  DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
                                  IF(
                                    tab_socios.adm_08 = '3',
                                    '\nSócio Administrador',
                                    ''
                                  )
                                ) AS NOME_SOCIO,
                                IF(
                                  ISNULL(tab_socios.cotas_socio_08),
                                  '0.00',
                                  tab_socios.cotas_socio_08
                                ) AS COTAS_SOCIO,
                                tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
                                tab_cidades.nome_cidade AS CIDADE_SOCIO,
                                tab_estados.sigla_estado AS ESTADO_SOCIO,
                                CONCAT(
                                  tab_pessoas.telefone_celular,
                                  ' ',
                                  tab_pessoas.telefone_fixo
                                ) AS TELEFONE_SOCIO,
                                tab_pessoas.email_usuario AS EMAIL_SOCIO,
                                tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
                                tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
                              FROM
                                tab_animais
                                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
                                JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
                                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                                LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
                                LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
                                JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                                JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_08
                                LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
                                LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
                              WHERE
                                tab_animais.id_tipo_animal = '$id_tipo_animal'
                                AND tab_animais.id_situacao = '$id_situacao'
                                AND (
                                  tab_animais.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.marca LIKE '%$palavra_chave%'
                                  OR tab_animais.registro_associacao LIKE '%$palavra_chave%'
                                  OR tab_grupo_animais.descricao LIKE '%$palavra_chave%'
                                  OR tab_pai_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_mae_animal.nome LIKE '%$palavra_chave%'
                                  OR tab_animais.chip LIKE '%$palavra_chave%'
                                  OR tab_socios.informacoes_diversas LIKE '%$palavra_chave%'
                                  OR tab_animais.informacoes_diversas LIKE '%$palavra_chave%'
                                )
                                AND tab_animais.id_usuario_sistema = '$id_proprietario'
                                $filtro_grupo
                              GROUP BY
                                tab_animais.id_animal
                              ORDER BY
                                tab_animais.nome ASC
                            )
                            ORDER BY
                              NOME_ANIMAL ASC,
                              NOME_SOCIO ASC
                          ) AS tab_socios_animais
                        HAVING
                          COTAS_ANIMAL < 100
                          AND COTAS_ANIMAL > 0";

            $pdo = $this->conn->conectar();
            $res = $pdo->query($query_sql);


            $res->execute();
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
            if (count($dados) <= 0) return  sucesso("Nenhum Sócio foi localizado!");
            
            $somatorio = [
                "TOTAL_SOCIOS" => count($dados)
            ];

            return sucesso("", ["dados"=>$dados, "resumo"=>$somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }





    
    public function listar_plantel(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();

        $id_proprietario = (int)trim($params['id_proprietario']);
        $palavra_chave = trim($params['palavra_chave']);
        $tipo_baixa = (int)trim($params['tipo_baixa']);
        $situacao = (int)trim($params['situacao']);
        $id_raca = (int)trim($params['id_raca']);
        $grupo = (int)trim($params['grupo']);
        $sexo = (int)trim($params['sexo']);

        $url_fotos = URL_FOTOS;

        if (
            $sexo <= 0 ||
            $grupo <= 0 ||
            $situacao <= 0 ||
            $tipo_baixa <= 0
        ) return erro("Parâmetros inválidos ou faltantes!");
        
        try {

            // Trata os parâmetros recebidos para construção da Query

            // Filtro raca, ticket 2686
            $filtro_raca = $id_raca ? " AND tab_animais.id_raca = {$id_raca} " : '';

            // Define o Grupo
            $filtro_grupo = (int)$grupo == 99 ? "" : " AND tab_animais.id_grupo = '$grupo'  ";

            // Define o Tipo de Baixa  
            $filtro_tipo_baixa = (int)$tipo_baixa == 5 ?  "" : "";
            $filtro_tipo_baixa = (int)$tipo_baixa == 1 ? " AND (tab_socios.cotas_socio_01 IS NULL OR tab_socios.cotas_socio_01 > '0') AND tab_animais.id_situacao_vida = '15' " : '';
            $filtro_tipo_baixa = (int)$tipo_baixa == 2 ? " AND (tab_socios.cotas_socio_01 IS NULL OR tab_socios.cotas_socio_01 = '0') AND tab_compras_vendas_animais.id_situacao_negocio = '42' " : '';
            $filtro_tipo_baixa = (int)$tipo_baixa == 3 ? " AND tab_animais.id_situacao_vida = '16'  " : '';
            $filtro_tipo_baixa = (int)$tipo_baixa == 4 ? " AND tab_animais.id_vender = '14' " : '';
            
            // Define o Sexo
            $filtro_sexo = (int)$sexo == 4 ? " AND tab_animais.id_situacao_macho_castrado = '7' " : " AND tab_animais.id_sexo = '$sexo' ";
            $filtro_sexo = (int)$sexo == 5 ? "" : $filtro_sexo;

            // Define a situação
            $filtro_situacao = (int)$situacao == 3 ? "" : " AND tab_animais.id_situacao = '$situacao'  ";

            // echo "$filtro_grupo\n";
            // echo "$filtro_tipo_baixa\n";
            // echo "$filtro_sexo\n";
            // echo "$filtro_situacao\n";
            // echo "$filtro_raca\n";
            // exit;

            $FILTRO_PALAVRA_CHAVE = '';

            if ( strlen($palavra_chave) > 3 ) {
                $FILTRO_PALAVRA_CHAVE =
                "   AND ( 
                        tab_animais.nome                    LIKE '%{$palavra_chave}%'
                        OR tab_animais.chip                 LIKE '%{$palavra_chave}%'
                        OR tab_animais.marca                LIKE '%{$palavra_chave}%'
                        OR tab_pai_animal.nome              LIKE '%{$palavra_chave}%'
                        OR tab_mae_animal.nome              LIKE '%{$palavra_chave}%'
                        -- OR tab_lotes.descricao              LIKE '%{$palavra_chave}%'
                        -- OR tab_localizacoes.descricao       LIKE '%{$palavra_chave}%'
                        OR tab_grupo_animais.descricao      LIKE '%{$palavra_chave}%'
                        OR tab_animais.registro_associacao  LIKE '%{$palavra_chave}%'
                        OR tab_animais.informacoes_diversas LIKE '%{$palavra_chave}%'
                        -- OR tab_controle_sanitario.descricao LIKE '%{$palavra_chave}%'
                    )
                ";
            }
            

            $connect = $this->conn->conectar();
            $query_sql = 
            "   SELECT  
                    tab_animais.id_animal as ID_ANIMAL,
                    UPPER(tab_grupo_animais.descricao) as GRUPO_ANIMAL,  
                    tab_animais.nome as NOME_ANIMAL,
                    tab_racas.descricao AS RACA_ANIMAL,
                    tab_racas.id_raca AS ID_RACA_ANIMAL, 
                    UPPER(tab_animais.marca) as MARCA_ANIMAL, 
                    UPPER(tab_sexos.sexo_animal) as SEXO_ANIMAL, 
                    DATE_FORMAT(tab_animais.data_nascimento, '%d/%m/%Y') as NASCIMENTO_ANIMAL, 
                    tab_pai_animal.nome as PAI_ANIMAL, 
                    tab_mae_animal.nome as MAE_ANIMAL, 
                    tab_animais.registro_associacao as REGISTRO_ANIMAL, 
                    UPPER(tab_situacoes.descricao) as DESCRICAO_SITUACAO_ANIMAL,  
                    IF(ISNULL(tab_socios.cotas_socio_01),'0.00',tab_socios.cotas_socio_01) as COTAS_ANIMAL,
                    IF(tab_animais.foto_perfil_animal = 'sem_foto.jpg' OR tab_animais.foto_perfil_animal IS NULL ,null,CONCAT('$url_fotos',tab_animais.foto_perfil_animal)) as FOTO_ANIMAL,
                    (
                        CASE 
                            WHEN tab_socios.cotas_socio_01 IS NULL OR tab_socios.cotas_socio_01 > 0 AND tab_animais.id_situacao_vida = '15' THEN '1' 
                            WHEN tab_socios.cotas_socio_01 IS NULL OR tab_socios.cotas_socio_01 = 0 AND tab_compras_vendas_animais.id_situacao_negocio = '42' THEN '2'
                            WHEN tab_animais.id_situacao_vida = '16' THEN '3'
                            WHEN tab_animais.id_vender = '14' THEN '4'
                            ELSE '5'
                        END
                    ) as TIPO_BAIXA 
                FROM tab_animais  
                JOIN tab_racas     ON tab_animais.id_raca = tab_racas.id_raca  
                JOIN tab_sexos     ON tab_sexos.id_sexo = tab_animais.id_sexo   
                JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao   
                JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
                LEFT JOIN tab_animais  AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai  
                LEFT JOIN tab_animais  AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae   
                LEFT JOIN tab_socios   ON tab_socios.id_animal = tab_animais.id_animal   
                LEFT JOIN tab_compras_vendas_animais ON tab_compras_vendas_animais.id_produto_animal = tab_animais.id_animal

                -- LEFT JOIN tab_localizacoes ON tab_localizacoes.id_localizacao = tab_animais.id_localizacao
                -- LEFT JOIN tab_animais_nos_lotes ON tab_animais_nos_lotes.id_animal = tab_animais.id_animal
                -- LEFT JOIN tab_lotes ON tab_lotes.id_lote = tab_animais_nos_lotes.id_lote
                -- LEFT JOIN tab_animais_manejo ON tab_animais_manejo.id_animal = tab_animais.id_animal
                -- LEFT JOIN tab_controle_sanitario ON tab_controle_sanitario.id_manejo = tab_animais_manejo.id_manejo
                WHERE (
                    tab_animais.id_usuario_sistema = '{$id_proprietario}' 
                    AND tab_animais.id_situacao_cadastro = '11'
                    AND tab_animais.id_consignacao = '112' 
                    
                    $filtro_sexo
                    $filtro_raca
                    $filtro_grupo
                    $filtro_situacao
                    $filtro_tipo_baixa

                    {$FILTRO_PALAVRA_CHAVE}
                )
                GROUP BY tab_animais.id_animal  
                ORDER BY tab_animais.nome ASC
            ";
            
            $stmt = $connect->prepare($query_sql);
            if(!$stmt) {
                return erro("Erro: {$connect->errno} - {$connect->error}", 500);
            }            
            if( !$stmt->execute() ) {
                return erro("SQLSTATE: #". $stmt->errorInfo()[2], 500);
            }
            if ( $stmt->rowCount() <= 0 ) {
                return erro("Localização não cadastrada!");
            }

            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $num_resultados =  count($dados);
            
            if ($num_resultados <= 0) return sucesso("Nenhum animal foi localizado!");
            
            # Dados empilhados para ser convertido em JSON
            $total_machos = 0;
            $total_femeas = 0;

            foreach ($dados as $key => $value) {
                # SOMA OS ANIMAIS
                trim($value['SEXO_ANIMAL']) == "MACHO" ? $total_machos++ : $total_femeas++;

                $dados[$key]['CONTADOR'] =  $key+1;
            }

            # Array do Somatório
            $somatorio = [
                "TOTAL_GERAL_FILHOS" => (int)$key+1,
                "TOTAL_MACHOS" => (int)$total_machos,
                "TOTAL_FEMEAS" => (int)$total_femeas
            ];

            return sucesso("{$num_resultados} RESULTADOS ENCONTRADOS!", ["dados"=>$dados, "resumo"=>$somatorio]);

        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }



    public function listar_racas(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = @$params['id_proprietario'];

        if (!@$id_proprietario) return erro("Haras com identificação incorreta!");
        
        try {

            $query_sql = 
                        "SELECT 
                        (SELECT 'radio')  AS 'type',
                        tab_racas.descricao AS label,
                        tab_racas.id_raca AS value
                    FROM
                        tab_grupo_animais
                            JOIN
                        tab_animais ON tab_animais.id_grupo = tab_grupo_animais.id_grupo_animal
                            JOIN
                        tab_racas ON tab_animais.id_raca = tab_racas.id_raca
                            LEFT JOIN
                        tab_socios ON tab_socios.id_animal = tab_animais.id_animal
                    WHERE
                        (tab_socios.cotas_socio_01 IS NULL
                            OR tab_socios.cotas_socio_01 > '0')
                            AND tab_animais.id_situacao_vida = '15'
                            AND tab_animais.id_situacao_cadastro = '11'
                            AND tab_animais.id_situacao = '1'
                            AND tab_animais.id_usuario_sistema = :ID_PROPRIETARIO
                    GROUP BY tab_racas.id_raca";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($dados) <= 0) return sucesso("Você ainda não possui nenhum animal cadastrado em seu Plantel!");
                    
            return sucesso("", ["dados"=>$dados]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }
    public function menu_plantel(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = @$params['id_proprietario'];
        $id_raca            = @$params['id_raca'];

        if (!@$id_proprietario) return erro("Haras com identificação incorreta!");
        try {

            $filtro_raca = $id_raca ? "tab_animais.id_raca = {$id_raca} AND" : ''; 
            $query_sql = 
                        "SELECT  
                        tab_grupo_animais.id_grupo_animal AS ID_GRUPO, 
                        tab_grupo_animais.descricao_plural AS NOME_GRUPO, 
                        COUNT(tab_animais.id_animal) as QUANTIDADE_GRUPO,
                        tab_racas.id_raca AS ID_RACA,
                        UPPER(tab_racas.descricao) AS NOME_RACA
                    FROM tab_grupo_animais  
                        JOIN tab_animais ON tab_animais.id_grupo = tab_grupo_animais.id_grupo_animal
                        LEFT JOIN tab_racas ON tab_animais.id_raca = tab_racas.id_raca 
                        LEFT JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal  
                    WHERE 
                        (tab_socios.cotas_socio_01 IS NULL OR tab_socios.cotas_socio_01 > '0') AND
                        tab_animais.id_situacao_vida = '15' AND
                        tab_animais.id_situacao_cadastro = '11' AND
                        tab_animais.id_situacao = '1' AND
                        {$filtro_raca} -- Filtro raca, ticket 2686 
                        tab_animais.id_usuario_sistema = :ID_PROPRIETARIO
                    GROUP BY tab_grupo_animais.id_grupo_animal";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($dados) <= 0) return sucesso("Você ainda não possui nenhum animal cadastrado em seu Plantel!");
            //$resposta = json_encode(["codigo" => false,"status" => false, "message" => "Você ainda não possui nenhum animal cadastrado em seu Plantel!", "data" => ""]);
            
            $totalizador = 0;
            foreach ($dados as $key => $value) {
                // Faz o Somatório dos Resumo
                $totalizador += $value['QUANTIDADE_GRUPO'];
                $dados[$key]['CONTADOR'] =  $key+1;
            }

            $somatorio = [
                "TOTAL_PLANTEL" => $totalizador
            ];
                
            return sucesso("", ["dados"=>$dados, "resumo"=>$somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
        
    }













    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	/**
	 * Método cadastro()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
    public function cadastro(ServerRequestInterface $request) {

		// $post = body_params()
        $post = (object)$request->getParsedBody();
        if( !in_array($post->id_situacao_cadastro, [11, 12]) ) return erro('campo [SITUAÇÃO/TIPO DE CADASTRO] inválido!');

        # SE FOR CADASTRO DE ANIMAL AUXILIAR...
        if ( $post->id_situacao_cadastro == 12  ) {
            $post->id_raca = 1; # Raça Não Informada)
            $post->id_vender = 13; # Não vender
            $post->id_pelagem = 1; # Pelagem desconhecida
            $post->id_situacao = 1; # Ativo
            $post->id_classificacao = 1; # Sem classificação
            $post->id_situacao_vida = 15; # Vivo
            $post->data_nascimento = '1970-01-01';
            $post->id_situacao_macho_castrado = 6; # Inteiro(6) / Castrado

            $post->id_criador = $post->id_proprietario;
            $post->id_proprietario_animal = $post->id_proprietario;
        }
        

        $post->id_pai = isset($post->id_pai) && !vazio($post->id_pai) ? (int)$post->id_pai : null;
        $post->id_mae = isset($post->id_mae) && !vazio($post->id_mae) ? (int)$post->id_mae : null;

        $post->toe_animal = isset($post->toe_animal) && !vazio($post->toe_animal) ? trim($post->toe_animal) : null;
        $post->tod_animal = isset($post->tod_animal) && !vazio($post->tod_animal) ? trim($post->tod_animal) : null;

        if( !in_array($post->id_vender, [13, 14]) ) return erro('campo [COLOCAR À VENDA] inválido!');

        if( vazio($post->id_raca) ) return erro('campo [RAÇA] obrigatório!');
        if( !in_array($post->id_sexo, [1, 2, 3]) ) return erro('campo [SEXO] inválido!'); # Não Informado(1), Macho(2), Fêmea(3)
        if( vazio($post->id_grupo) ) return erro('campo [GRUPO / CATEGORIA] obrigatório!');
        if( vazio($post->id_pelagem) ) return erro('campo [PELAGEM] obrigatório!');
        if( !in_array($post->id_situacao, [1, 2]) ) return erro('campo [SITUAÇÃO] inválido!');
        
        if( vazio($post->id_tipo_animal) ) return erro('campo [TIPO ANIMAL] obrigatório!');
        if( !in_array($post->id_tipo_animal, [10, 20, 30, 40, 50, 60])) return erro('campo [TIPO ANIMAL] inválido!');
        
        if( vazio($post->id_classificacao) ) return erro('campo [CLASSIFICAÇÃO] obrigatório!');
        
        if( !in_array($post->id_situacao_vida, [15, 16]) ) return erro('campo [SITUAÇÃO DE VIDA] inválido!');

        # Situação de vida como MORTO (16)
        if ( $post->id_situacao_vida == 16 ) {
            if ( vazio($post->data_morte) ) return erro('Informe a [DATA DA MORTE] do animal!');
            if ( !data_valida($post->data_morte) ) return erro('[DATA DA MORTE] inválida!');
            if ( strtotime($post->data_morte) > strtotime(DATA_ATUAL) ) return erro('[DATA DA MORTE] inválida! (data do futuro)');

            if ( vazio($post->causa_morte) )  return erro('Informe a [CAUSA DA MORTE]!');
        }

        // if( isset($post->id_localizacao) && vazio($post->id_localizacao) ) return erro('campo [LOCALIZAÇÃO] inválido!');
        if ( vazio($post->nome) ) return erro('Campo [NOME] inválido!');
        
        # DATA DE NASCIMENTO
        if ( vazio($post->data_nascimento) ) return erro('Campo [DATA DE NASCIMENTO] obrigatório!');
        if ( !data_valida($post->data_nascimento) ) return erro('Campo [DATA DE NASCIMENTO] inválido!');
        if ( strtotime($post->data_nascimento) > strtotime(DATA_ATUAL) ) return erro('[DATA DE NASCIMENTO] inválida! (data do futuro)');

        if ( isset($post->id_criador) && vazio($post->id_criador) ) return erro('Campo [CRIADOR] inválido!');
        if ( vazio($post->id_proprietario_animal) ) return erro('Campo [PROPRIETÁRIO DO ANIMAL] obrigatório!');

        if( isset($post->id_situacao_macho_castrado) ) {
            if( !in_array($post->id_situacao_macho_castrado, [6, 7]) ) return erro('campo [CASTRADO] inválido!');

            # Se o sexo for [FÊMEA], e o animal vir como [CASTRADO] ...
            if( $post->id_sexo == 3 && $post->id_situacao_macho_castrado == 7 ) return erro('Animais do sexo [FÊMEA] não podem estar com situação de [CASTRADO]!');
        }
        
        if( isset($post->id_dna) && !in_array($post->id_dna, [74, 75]) ) return erro('campo [DNA] inválido!');
        if ( isset($post->id_consignacao) && !in_array($post->id_consignacao, [112, 113]) ) return erro('campo [CONSIGNAÇÃO] inválido!');


        $post->id_localizacao = isset($post->id_localizacao) && !vazio($post->id_localizacao) ? $post->id_localizacao : NULL;

        $post->chip                 = isset($post->chip) && !vazio($post->chip) ? $post->chip : NULL;
        $post->marca                = isset($post->marca) && !vazio($post->marca) ? $post->marca : 'SEM MARCA';
        $post->valor_mercado        = isset($post->valor_mercado) && !vazio($post->valor_mercado) ? $post->valor_mercado : '0.00';
        $post->grau_de_sangue       = isset($post->grau_de_sangue) && !vazio($post->grau_de_sangue) ? $post->grau_de_sangue : NULL;
        $post->registro_associacao  = isset($post->registro_associacao) && !vazio($post->registro_associacao) ? $post->registro_associacao : 'SEM REGISTRO';
        $post->informacoes_diversas = isset($post->informacoes_diversas) && !vazio($post->informacoes_diversas) ? $post->informacoes_diversas : NULL;

        $post->id_dna = !vazio($post->id_dna) ? $post->id_dna : 75;
        $post->id_consignacao = !vazio($post->id_consignacao) ? $post->id_consignacao : 112;

        // return sucesso("MÉTODO EM MANUTENÇÃO!", $post);

        if ( (int)$post->id_animal <= 0 ) {
            return $this->insert($post);
        }
        else {
            return $this->update($post);
        }
    }














    



    
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método insert()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
    private function insert($post) {

        $connect = $this->conn->conectar();
        $connect->beginTransaction();

        $query_insert_animal =
		"	INSERT INTO tab_animais (
                id_usuario_sistema,
                id_tipo_animal,
                id_situacao,
                id_raca,
                id_sexo,
                id_pelagem,
                id_grupo,
                id_situacao_cadastro,
                id_classificacao,
                id_situacao_vida,
                id_localizacao,
                id_vender,
                
                id_pai,
                id_mae,

                id_situacao_macho_castrado,
                id_dna,
                id_proprietario,
                id_criador,
                id_consignacao,
                cod_associacao,
                nome,
                data_nascimento,
                registro_associacao,
                chip,
                marca,
                grau_de_sangue,
                valor_mercado,
                informacoes_diversas,
                toe_animal,
                tod_animal,

                data_morte,
                causa_morte,

                DATA_ATUALIZACAO,
                DATA_CRIACAO,
                ID_USUARIO_CRIACAO,
                ID_USUARIO_ATUALIZACAO
			) 
			VALUES (
                :id_usuario_sistema, -- 'DONO' DO HARAS/FAZENDA
                
                :id_tipo_animal,
                :id_situacao,
                :id_raca,
                :id_sexo,
                :id_pelagem,
                :id_grupo,
                :id_situacao_cadastro,
                :id_classificacao,
                :id_situacao_vida,
                :id_localizacao,
                :id_vender,
                
                :id_pai,
                :id_mae,

                :id_situacao_macho_castrado,
                :id_dna,
                :id_proprietario, -- DONO DO ANIMAL
                :id_criador,
                :id_consignacao,
                :cod_associacao,
                upper(:nome),
                :data_nascimento,
                :registro_associacao,
                :chip,
                upper(:marca),
                :grau_de_sangue,
                :valor_mercado,
                :informacoes_diversas,
                :toe_animal,
                :tod_animal,

                :data_morte,
                :causa_morte,

                CURDATE(), -- DATA_ATUALIZACAO,
                CURDATE(), -- DATA_CRIACAO,
                :ID_USUARIO_CRIACAO, -- ID DE QUEM CRIOU O REGISTRO
                :ID_USUARIO_ATUALIZACAO
			)
        ";

        $stmt = $connect->prepare($query_insert_animal);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }

        
        # BIND PARAMS
        {
            $stmt->bindParam(':id_usuario_sistema', $post->id_proprietario, PDO::PARAM_INT); # DONO HARAS/FAZENDA/EMRESA
            $stmt->bindParam(':id_tipo_animal', $post->id_tipo_animal);
            $stmt->bindParam(':id_situacao', $post->id_situacao);
            $stmt->bindParam(':id_raca', $post->id_raca);
            $stmt->bindParam(':id_sexo', $post->id_sexo);
            $stmt->bindParam(':id_pelagem', $post->id_pelagem);
            $stmt->bindParam(':id_grupo', $post->id_grupo);
            $stmt->bindParam(':id_situacao_cadastro', $post->id_situacao_cadastro);
            $stmt->bindParam(':id_classificacao', $post->id_classificacao);
            $stmt->bindParam(':id_situacao_vida', $post->id_situacao_vida);
            $stmt->bindParam(':id_localizacao', $post->id_localizacao);
            $stmt->bindParam(':id_vender', $post->id_vender);
            $stmt->bindParam(':id_pai', $post->id_pai);
            $stmt->bindParam(':id_mae', $post->id_mae);
            $stmt->bindParam(':id_situacao_macho_castrado', $post->id_situacao_macho_castrado);
            $stmt->bindParam(':id_dna', $post->id_dna);
            $stmt->bindParam(':id_proprietario', $post->id_proprietario_animal); # DONO DO ANIMAL
            $stmt->bindParam(':id_criador', $post->id_criador);
            $stmt->bindParam(':id_consignacao', $post->id_consignacao);
            $stmt->bindParam(':cod_associacao', $post->cod_associacao);
            $stmt->bindParam(':nome', $post->nome);
            $stmt->bindParam(':data_nascimento', $post->data_nascimento);
            $stmt->bindParam(':registro_associacao', $post->registro_associacao);
            $stmt->bindParam(':chip', $post->chip);
            $stmt->bindParam(':marca', $post->marca);
            $stmt->bindParam(':grau_de_sangue', $post->grau_de_sangue);
            $stmt->bindParam(':valor_mercado', $post->valor_mercado);
            $stmt->bindParam(':informacoes_diversas', $post->informacoes_diversas);
            $stmt->bindParam(':toe_animal', $post->toe_animal);
            $stmt->bindParam(':tod_animal', $post->tod_animal);
            $stmt->bindParam(':data_morte', $post->data_morte);
            $stmt->bindParam(':causa_morte', $post->causa_morte);
        }
        
        $stmt->bindParam(':ID_USUARIO_CRIACAO', $post->id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':ID_USUARIO_ATUALIZACAO', $post->id_usuario, PDO::PARAM_INT);

        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ !modo_dev() ? 2 : 1 ], 500);
        }
        if ( $stmt->rowCount() <= 0 ) {   
            return erro("Animal Não cadastrado...");
        }
        $post->ID_ANIMAL = $connect->lastInsertId();
        $connect->commit();

        $_SESSION['debug'] = "Animal de registro [{$post->ID_ANIMAL}] adicionado!";
        
        # ARMAZENANDO A IMAGEM DO ANIMAL NO DIRETÓRIO DO SERVER
        if ( !vazio($post->foto_base64) ) {
            $res = json_decode(@$this->foto_base64_arq($post->ID_ANIMAL, $post->foto_base64));
            if ( !$res->codigo ) {
                return json_encode($res);
            }
        }

        return sucesso("ANIMAL CADASTRADO COM SUCESSO!", [$post]);
    } // insert














    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método update()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
    private function update($post) {

        $connect = $this->conn->conectar();
        $connect->beginTransaction();
        $query_update =
		"   UPDATE tab_animais SET
                id_tipo_animal = :id_tipo_animal,
                id_situacao    = :id_situacao,
                id_raca        = :id_raca,
                id_sexo        = :id_sexo,
                id_pelagem     = :id_pelagem,
                id_grupo       = :id_grupo,
                id_situacao_cadastro = :id_situacao_cadastro,
                id_classificacao     = :id_classificacao,
                id_situacao_vida     = :id_situacao_vida,
                id_localizacao       = :id_localizacao,
                id_vender            = :id_vender,
                
                id_pai = :id_pai,
                id_mae = :id_mae,

                id_situacao_macho_castrado = :id_situacao_macho_castrado,
                id_dna          = :id_dna,
                id_proprietario = :id_proprietario_animal, -- DONO DO ANIMAL
                id_criador      = :id_criador,
                id_consignacao  = :id_consignacao,
                cod_associacao  = :cod_associacao,
                nome            = upper(:nome),
                data_nascimento = :data_nascimento,
                registro_associacao  = :registro_associacao,
                chip                 = :chip,
                marca                = upper(:marca),
                grau_de_sangue       = :grau_de_sangue,
                valor_mercado        = :valor_mercado,
                informacoes_diversas = :informacoes_diversas,
                toe_animal           = :toe_animal,
                tod_animal           = :tod_animal,

                data_morte = :data_morte,
                causa_morte = :causa_morte,

                DATA_ATUALIZACAO = CURDATE(), -- DATA_ATUALIZACAO,
                ID_USUARIO_ATUALIZACAO = :ID_USUARIO_ATUALIZACAO
            WHERE (
                id_animal = :id_animal AND
                id_usuario_sistema = :id_proprietario -- DONO DO HARAS/FAZENDA/EMPRESA
            )
		";
        $stmt = $connect->prepare($query_update);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }

        # BIND PARAMS
        {

            $stmt->bindParam(':id_animal', $post->id_animal, PDO::PARAM_INT);
            $stmt->bindParam(':id_proprietario', $post->id_proprietario, PDO::PARAM_INT);
            $stmt->bindParam(':ID_USUARIO_ATUALIZACAO', $post->id_usuario, PDO::PARAM_INT);

            $stmt->bindParam(':id_tipo_animal', $post->id_tipo_animal);
            $stmt->bindParam(':id_situacao', $post->id_situacao);
            $stmt->bindParam(':id_raca', $post->id_raca);
            $stmt->bindParam(':id_sexo', $post->id_sexo);
            $stmt->bindParam(':id_pelagem', $post->id_pelagem);
            $stmt->bindParam(':id_grupo', $post->id_grupo);
            $stmt->bindParam(':id_situacao_cadastro', $post->id_situacao_cadastro);
            $stmt->bindParam(':id_classificacao', $post->id_classificacao);
            $stmt->bindParam(':id_situacao_vida', $post->id_situacao_vida);
            $stmt->bindParam(':id_localizacao', $post->id_localizacao);
            $stmt->bindParam(':id_vender', $post->id_vender);
            $stmt->bindParam(':id_pai', $post->id_pai);
            $stmt->bindParam(':id_mae', $post->id_mae);
            $stmt->bindParam(':id_situacao_macho_castrado', $post->id_situacao_macho_castrado);
            $stmt->bindParam(':id_dna', $post->id_dna);
            $stmt->bindParam(':id_proprietario_animal', $post->id_proprietario_animal); # DONO DO ANIMAL
            $stmt->bindParam(':id_criador', $post->id_criador);
            $stmt->bindParam(':id_consignacao', $post->id_consignacao);
            $stmt->bindParam(':cod_associacao', $post->cod_associacao);
            $stmt->bindParam(':nome', $post->nome);
            $stmt->bindParam(':data_nascimento', $post->data_nascimento);
            $stmt->bindParam(':registro_associacao', $post->registro_associacao);
            $stmt->bindParam(':chip', $post->chip);
            $stmt->bindParam(':marca', $post->marca);
            $stmt->bindParam(':grau_de_sangue', $post->grau_de_sangue);
            $stmt->bindParam(':valor_mercado', $post->valor_mercado);
            $stmt->bindParam(':informacoes_diversas', $post->informacoes_diversas);
            $stmt->bindParam(':toe_animal', $post->toe_animal);
            $stmt->bindParam(':tod_animal', $post->tod_animal);
            $stmt->bindParam(':data_morte', $post->data_morte);
            $stmt->bindParam(':causa_morte', $post->causa_morte);
        }

        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
        }
        $connect->commit();

        $_SESSION['debug'] = "Animal de registro [{$post->id_animal}] ATUALIZADO!";
        
        # ARMAZENANDO A IMAGEM DO ANIMAL NO DIRETÓRIO DO SERVER
        if ( !vazio($post->foto_base64) ) {
            $_SESSION['update_img'] = true;
            $res = json_decode(@$this->foto_base64_arq($post->id_animal, $post->foto_base64));
            if ( !$res->codigo ) {
                return json_encode($res);
            }
        }

        $_SESSION['debug'] .= isset($_SESSION['update_img']) ? '- IMAGEM ATUALIZADA' : '';

        $sub_msg = ($stmt->rowCount() <= 0 && !isset($_SESSION['update_img'])) ? ' - NENHUMA INFORMAÇÃO ALTERADA!' : '';
        return sucesso("CADASTRO ATUALIZADO COM SUCESSO!{$sub_msg}");
    }







    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método foto_base64_arq() - converte um base64 para arquivo de imagem, armazana o mesmo e atualiza o campo [foto_perfil_animal] com este arquivo
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
    private function foto_base64_arq($id_animal, $foto_base64) {

        if ( vazio($id_animal) )   return erro("ANIMAL DA IMG NÃO IDENTIFICADO!!");
        if ( vazio($foto_base64) ) return erro("BASE64 DA IMG NÃO INFORMADO!");

        $foto_base64 = trim($foto_base64);
        if ( substr($foto_base64, 0, 4) != 'data' ) {
            $foto_base64 = "data:image/jpeg;base64,{$foto_base64}";
        }

        $nome_arquivo = 'animal_'. str_pad($id_animal, 6, '0', STR_PAD_LEFT) .'.jpg';
        
        if ( !@file_put_contents(PATH_UPLOAD_FOTOS . "/{$nome_arquivo}", file_get_contents($foto_base64)) ) {
            return erro("NÃO FOI POSSÍVEL ARMAZENAR A IMAGEM RECEBIDA!");
        }

        $nome_arquivo .= isset($_SESSION['update_img']) ? '?v='.date('dmyHis') : '';

        $connect = $this->conn->conectar();
        $query_update =
		"   UPDATE tab_animais SET
				foto_perfil_animal = :nome_arquivo
            WHERE id_animal = :id_animal
		";
        $stmt = $connect->prepare($query_update);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }

        $stmt->bindParam(':id_animal', $id_animal, PDO::PARAM_INT);
        $stmt->bindParam(':nome_arquivo', $nome_arquivo);

        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
        }
        if ( $stmt->rowCount() <= 0 ) {   
            // return erro("Imagem do Animal não cadastrada...");
        }
        
        return sucesso("Imagem cadastrada com sucesso!");
    }






    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método categorias()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
    public function categorias() {

        $connect = $this->conn->conectar();
        $query =
		"   SELECT 
                concat(id_grupo_animal) id_grupo_animal,
                upper(tab_grupo_animais.descricao) AS grupo,
                upper(tab_grupo_animais.descricao_plural) AS grupo_plural,
                
                concat(tab_grupo_animais.id_especie) id_especie,
                upper(tab_especies_animais.descricao) AS especie,
                upper(tab_especies_animais.descricao_plural) AS especie_plural
            FROM tab_grupo_animais
            JOIN tab_especies_animais ON tab_especies_animais.id_especie = tab_grupo_animais.id_especie
            ORDER BY tab_grupo_animais.id_especie, ordem
		";
        $stmt = $connect->prepare($query);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }
        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
        }
        if ( $stmt->rowCount() <= 0 ) {   
            return erro("Nenhum registro encontrado...");
        }

        $dados = [];
        foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $dado) {
            array_push($dados, numerics_json($dado));
        }
        
        return sucesso("{$stmt->rowCount()} RESULTADOS ENCONTRADOS!", $dados);
    }



    
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método localizacoes()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
    // public function localizacoes($post) {
    public function localizacoes(ServerRequestInterface $request) {

        // $post = body_params()
        $post = (object)$request->getParsedBody();

        $connect = $this->conn->conectar();
        $query =
		"  SELECT * FROM tab_localizacoes
            WHERE id_usuario_sistema = :id_proprietario -- DONO DO HARAS/FAZENDA/EMPRESA
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
            return erro("NENHUMA LOCALIZAÇÃO ENCONTRADA!");
        }

        return sucesso("{$stmt->rowCount()} RESULTADOS ENCONTRADOS!", $stmt->fetchAll(PDO::FETCH_OBJ));
    }




    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método pais()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
    public function pais(ServerRequestInterface $request) {

        $post = (object)$request->getParsedBody();

        $connect = $this->conn->conectar();
        if( !is_numeric($post->id_sexo) ) return erro('campo [SEXO] inválido! #0'); # Não Informado(1), Macho(2), Fêmea(3)

        if( !in_array($post->id_sexo, [0, 2, 3]) ) return erro('campo [SEXO] inválido!'); # Não Informado(1), Macho(2), Fêmea(3)

        $SUBQUERY_SEXO = $post->id_sexo > 0 ? " AND id_sexo = '{$post->id_sexo}' " : " AND id_sexo IN (2, 3) ";

        $query =
		"   SELECT 
                id_animal, nome, id_sexo
            FROM tab_animais
            WHERE (
                id_usuario_sistema = :id_proprietario
                AND id_situacao_vida = '15' -- VIVOS
                {$SUBQUERY_SEXO}
                AND id_situacao = '1'
            )
            ORDER BY nome ASC
		";
        $stmt = $connect->prepare($query);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }
        
        // $stmt->bindParam(':id_sexo', $post->id_sexo, PDO::PARAM_INT);
        $stmt->bindParam(':id_proprietario', $post->id_proprietario, PDO::PARAM_INT);

        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
        }
        if ( $stmt->rowCount() <= 0 ) {   
            return erro("NENHUM RESULTADO ENCONTRADO!", 404);
        }

        $array_sexos = [
            0 => 'MACHOS E FÊMEAS',
            1 => 'SEXO NÃO INFORMADO',
            2 => 'MACHOS',
            3 => 'FÊMEAS'
        ];

        return sucesso("{$stmt->rowCount()} RESULTADOS ENCONTRADOS! ({$array_sexos[$post->id_sexo]})", $stmt->fetchAll(PDO::FETCH_OBJ));
    }













    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
	 * Método cadastra_genealogia()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
    public function cadastro_genealogia(ServerRequestInterface $request) {

        $post = (object)$request->getParsedBody();

        if ( !is_numeric($post->id_animal) || (int)$post->id_animal <= 0 ) {
            msg_debug("CAMPO [ID_ANIMAL] INVÁLIDO!");
            return erro("Animal não identificado!", 400, $post);
        }

        
        $campos_genealogia = ['animal_11', 'animal_111', 'animal_112', 'animal_12', 'animal_121', 'animal_122', 'animal_21', 'animal_211', 'animal_212', 'animal_22', 'animal_221', 'animal_222'];
        $post_validade = (array)$post;

        // $post->animal_11 .= ' - ' . DATA_HORA_ATUAL;

        $num_campos_preenchidos = 0;
        foreach ($campos_genealogia as $campo_obrigatorio) {
            if ( !isset($post_validade[$campo_obrigatorio]) ) {
                return erro("Campo [{$campo_obrigatorio}] não informado!");
            }

            if ( !vazio($post_validade[$campo_obrigatorio]) ) {
                $num_campos_preenchidos++;
            }
        }

        if ($num_campos_preenchidos <= 0) return erro("Não é possível cadastrar uma Genealogia com todos os Campos Vazios!");

        foreach ($campos_genealogia as $campo_obrigatorio) {
            if ( isset($post->$campo_obrigatorio) && vazio($post->$campo_obrigatorio) ) {
                $post->$campo_obrigatorio = '*****';
            }
            else {
                $post->$campo_obrigatorio = trim($post->$campo_obrigatorio);
            }
        }

        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $connect = $this->conn->conectar();
        $query =
		"   SELECT 
                tab_animais.id_animal, nome, id_usuario_sistema, id_genealogia
            FROM tab_animais
            LEFT JOIN tab_genealogias ON tab_genealogias.id_animal = tab_animais.id_animal
            WHERE tab_animais.id_animal = :id_animal
            GROUP BY tab_animais.id_animal
		";
        $stmt = $connect->prepare($query);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }
        $stmt->bindParam(':id_animal', $post->id_animal, PDO::PARAM_INT);
        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
        }
        if ( $stmt->rowCount() <= 0 ) {   
            msg_debug("ID DE ANIMAL {$post->id_animal} NÃO EXISTE NO BANCO!");
            return erro("ANIMAL INFORMADO NÃO EXISTE NA BASE DE DADOS!", 404);
        }
        
        $animal = $stmt->fetch(PDO::FETCH_OBJ);

        if ( (int)$animal->id_usuario_sistema != (int)$post->id_proprietario ) {
            msg_debug("ID DO ANIMAL '{$animal->nome}' PERTENCE AO PROPRIETÁRIO '{$animal->id_usuario_sistema}' E NÃO AO '$post->id_proprietario'!");
            return erro("Animal informado não presente ao SEU PLANTEL!", 404, $animal);
        }

        $GENEALOGIA_EXISTENTE = (int)$animal->id_genealogia > 0;



        # QUERYS DE INSERT/UPDATE
        {
            $query_update_genealogia =
            "   UPDATE tab_genealogias SET
                    animal_11  = upper(:animal_11),
                    animal_111 = upper(:animal_111),
                    animal_112 = upper(:animal_112),
                    animal_12  = upper(:animal_12),
                    animal_121 = upper(:animal_121),
                    animal_122 = upper(:animal_122),

                    animal_21  = upper(:animal_21),
                    animal_211 = upper(:animal_211),
                    animal_212 = upper(:animal_212),
                    animal_22  = upper(:animal_22),
                    animal_221 = upper(:animal_221),
                    animal_222 = upper(:animal_222),

                    DATA_ATUALIZACAO = CURDATE(),
                    ID_USUARIO_ATUALIZACAO = :ID_USUARIO_ATUALIZACAO
                WHERE (
                    id_genealogia = :id_genealogia
                    AND id_animal = :id_animal
                )
            ";
            


            $query_insert_genealogia =
            "	INSERT INTO tab_genealogias (
                    animal_11,
                    animal_111,
                    animal_112,
                    animal_12,
                    animal_121,
                    animal_122,

                    animal_21,
                    animal_211,
                    animal_212,
                    animal_22,
                    animal_221,
                    animal_222,
                    
                    id_animal,

                    DATA_CRIACAO,
                    DATA_ATUALIZACAO,
                    ID_USUARIO_CRIACAO,
                    ID_USUARIO_ATUALIZACAO
                ) 
                VALUES (
                    upper(:animal_11),
                    upper(:animal_111),
                    upper(:animal_112),
                    upper(:animal_12),
                    upper(:animal_121),
                    upper(:animal_122),

                    upper(:animal_21),
                    upper(:animal_211),
                    upper(:animal_212),
                    upper(:animal_22),
                    upper(:animal_221),
                    upper(:animal_222),

                    :id_animal,
                    
                    CURDATE(),
                    CURDATE(),
                    :ID_USUARIO_CRIACAO,
                    :ID_USUARIO_ATUALIZACAO
                )
            ";

        }

        $connect->beginTransaction();

        $stmt = $connect->prepare($GENEALOGIA_EXISTENTE ? $query_update_genealogia : $query_insert_genealogia);
        if(!$stmt) {
            return erro("Erro: {$connect->errno} - {$connect->error}", 500);
        }

        $stmt->bindParam(':animal_11', $post->animal_11);
        $stmt->bindParam(':animal_111', $post->animal_111);
        $stmt->bindParam(':animal_112', $post->animal_112);
        $stmt->bindParam(':animal_12', $post->animal_12);
        $stmt->bindParam(':animal_121', $post->animal_121);
        $stmt->bindParam(':animal_122', $post->animal_122);

        $stmt->bindParam(':animal_21', $post->animal_21);
        $stmt->bindParam(':animal_211', $post->animal_211);
        $stmt->bindParam(':animal_212', $post->animal_212);
        $stmt->bindParam(':animal_22', $post->animal_22);
        $stmt->bindParam(':animal_221', $post->animal_221);
        $stmt->bindParam(':animal_222', $post->animal_222);

        $stmt->bindParam(':id_animal', $post->id_animal, PDO::PARAM_INT);
        $stmt->bindParam(':ID_USUARIO_ATUALIZACAO', $post->id_usuario, PDO::PARAM_INT);

        if ( $GENEALOGIA_EXISTENTE ) {
            $stmt->bindParam(':id_genealogia', $animal->id_genealogia, PDO::PARAM_INT);
        }
        else {
            $stmt->bindParam(':ID_USUARIO_CRIACAO', $post->id_usuario, PDO::PARAM_INT);
        }

        if( !$stmt->execute() ) {
            return erro("SQLSTATE: #". $stmt->errorInfo()[ modo_dev() ? 1 : 2 ], 500);
        }
        $rowCount = $stmt->rowCount();
        $connect->commit();

        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        if ( $rowCount <= 0 && !$GENEALOGIA_EXISTENTE ) {
            msg_debug('NÃO CADASTRADA POR MOTIVOS DESCONHECIDOS -> VERIFIQUE!');
            return erro('GENEALOGIA NÃO CADASTRADA!');
        }
        if ( $GENEALOGIA_EXISTENTE ) {
            $sub_msg = $rowCount <= 0 ? ' - NENHUMA INFORMAÇÃO ALTERADA!' : '';
            return sucesso("GENEALOGIA ATUALIZADA COM SUCESSO!{$sub_msg}", $post);
        }
        return sucesso("GENEALOGIA CADASTRADA COM SUCESSO!", $post);
    }





} # AnimaisModel {}