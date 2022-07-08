<?php
use Psr\Http\Message\ServerRequestInterface;

class FinanceiroModel
{
    private $conn;
    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }
    public function listar_contas_pagar_receber(ServerRequestInterface $request)
    {
        $params              = (array)$request->getParsedBody();
        $id_proprietario     = $params['id_proprietario'];
        $palavra_chave       = $params['palavra_chave'];
        $data_inicial        = $params['data_inicial'];
        $data_final          = $params['data_final'];
        $tipo_movimentacao   = $params['tipo_movimentacao'];
        $grupo_financeiro    = $params['grupo_financeiro'];
        $situacao_financeiro = $params['situacao_financeiro'];

        if (!$tipo_movimentacao || !$data_inicial || !$id_proprietario || !$situacao_financeiro) return erro("Parâmetros inválidos ou faltantes!");

        try {

            // Define o Tipo de Movimentação
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 1 ? "" : "" ;
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 2 ? " tab_contas_pagar_receber.id_tipo_transacao = '91' AND " : $filtro_tipo_movimentacao;
            $filtro_tipo_movimentacao = (int)$tipo_movimentacao == 3 ? " tab_contas_pagar_receber.id_tipo_transacao = '92' AND " : $filtro_tipo_movimentacao;

            // Define o Grupo Financeiro
            $filtro_grupo_financeiro = (int)$grupo_financeiro == 0 ? "" : "";
            $filtro_grupo_financeiro = (int)$grupo_financeiro == 1 ? " tab_centros_custos_financeiro.id_centro_custo = '$grupo_financeiro' AND " : $filtro_grupo_financeiro;
            
            // Define a Situação do Financeiro
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 1 ? "" :"";
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 2 ? " (tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento >= CURDATE()) AND " : $filtro_situacao_financeiro;
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 3 ? " (tab_parcelas.id_situacao = '50') AND " : $filtro_situacao_financeiro;
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 4 ? " (tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento < CURDATE()) AND " : $filtro_situacao_financeiro;

            $query_sql = 
                        "SELECT  
                        tab_parcelas.id_parcela as ID_FINANCEIRO, 
                        DATE_FORMAT(tab_contas_pagar_receber.data_competencia, '%d/%m/%Y') as DATA_COMPETENCIA_FINANCEIRO, 
                        tab_contas_pagar_receber.id_tipo_transacao as ID_TIPO_FIANCEIRO,
                        UPPER(tab_tipos_transacoes.descricao) as TIPO_TRANSACAO_FINANCEIRO, 
                        tab_contas_pagar_receber.descricao as DESCRICAO_TRANSACAO_FINANCEIRO, 
                        tab_pessoas.nome_razao_social as CLIENTE_FORNECEDOR_FINANCEIRO, 
                        tab_contas_pagar_receber.nota_fiscal as NOTA_FISCAL_FINANCEIRO, 
                        tab_parcelas.sequencia_parcela as NUMERO_PARCELA_FINANCEIRO, 
                        tab_parcelas.valor_original as VALOR_ORIGINAL_FINANCEIRO, 
                        DATE_FORMAT(tab_parcelas.data_vencimento, '%d/%m/%Y') as DATA_VENCIMENTO_FINANCEIRO,
                        tab_parcelas.id_situacao as ID_SITUACAO_FINANCEIRO, 
                        CASE  
                            WHEN tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento >= CURDATE() THEN 'A VENCER'  
                            WHEN tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento < CURDATE() THEN CONCAT('VENCIDO HÁ ',DATEDIFF(CURDATE(),tab_parcelas.data_vencimento), ' DIA(S)')  
                            WHEN tab_parcelas.id_situacao = '50' AND tab_parcelas.data_vencimento >= tab_parcelas.data_pagamento THEN 'PAGO'  
                            WHEN tab_parcelas.id_situacao = '50' AND tab_parcelas.data_vencimento < tab_parcelas.data_pagamento THEN CONCAT('PAGO EM ATRASO - ',DATEDIFF(tab_parcelas.data_pagamento, tab_parcelas.data_vencimento), ' DIA(S)')  
                        END as DESCRICAO_SITUACAO_FINANCEIRO,
                        CASE  
                            WHEN tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento >= CURDATE() THEN 1  
                            WHEN tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento < CURDATE() THEN 2 
                            WHEN tab_parcelas.id_situacao = '50' AND tab_parcelas.data_vencimento >= tab_parcelas.data_pagamento THEN 3 
                            WHEN tab_parcelas.id_situacao = '50' AND tab_parcelas.data_vencimento < tab_parcelas.data_pagamento THEN 3  
                        END as ID_COR_SITUACAO_FINANCEIRO,  
                        IF(tab_parcelas.id_situacao = '50',DATE_FORMAT(tab_parcelas.data_pagamento, '%d/%m/%Y'),'AGUARDANDO') as DATA_PAGAMENTO_FINANCEIRO, 
                        UPPER(tab_tipos_meios_pagamentos.descricao) as MEIO_PAGAMENTO_FINANCEIRO, 
                        tab_parcelas.valor_desconto as VALOR_DESCONTO_FINANCEIRO, 
                        tab_parcelas.valor_multa_juros as VALOR_JUROS_MULTA_FINANCEIRO, 
                        (tab_parcelas.valor_original - tab_parcelas.valor_desconto + tab_parcelas.valor_multa_juros) as VALOR_TOTAL_FINANCEIRO, 
                        tab_grupos_financeiros.descricao as GRUPO_FINANCEIRO_FINANCEIRO, 
                        tab_contas.descricao as CONTA_BANCARIA_FINANCEIRO 
                    FROM tab_parcelas  
                        JOIN tab_centros_custos_financeiro ON tab_centros_custos_financeiro.id_parcela = tab_parcelas.id_parcela
                                      JOIN tab_contas_pagar_receber ON tab_contas_pagar_receber.id_conta_pagar_receber = tab_parcelas.id_conta_pagar_receber	 
                        JOIN tab_grupos_financeiros ON tab_grupos_financeiros.id_grupo_financeiro = tab_centros_custos_financeiro.id_centro_custo  
                        JOIN tab_tipos_transacoes ON tab_tipos_transacoes.id_tipo_transacao = tab_grupos_financeiros.id_tipo_grupo_financeiro  
                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_contas_pagar_receber.id_cliente_fornecedor  
                        JOIN tab_tipos_meios_pagamentos ON tab_tipos_meios_pagamentos.id_meio_pagamento = tab_parcelas.id_forma_pagamento  
                        JOIN tab_contas ON tab_contas.id_conta = tab_parcelas.id_conta  
                    WHERE  
                        $filtro_tipo_movimentacao
                        $filtro_grupo_financeiro
                        $filtro_situacao_financeiro
                        tab_parcelas.data_vencimento BETWEEN '$data_inicial' AND '$data_final' AND  
                        ( 
                            tab_contas_pagar_receber.descricao LIKE '%$palavra_chave%' OR  
                            tab_contas_pagar_receber.informacoes_diversas LIKE '%$palavra_chave%' OR  
                            tab_tipos_transacoes.descricao LIKE '%$palavra_chave%' OR  
                            tab_pessoas.nome_razao_social LIKE '%$palavra_chave%' OR  
                            tab_contas.descricao LIKE '%$palavra_chave%' OR  
                            tab_parcelas.informacoes_diversas LIKE '%$palavra_chave%' OR  
                            tab_tipos_meios_pagamentos.descricao LIKE '%$palavra_chave%' OR  
                            tab_contas_pagar_receber.nota_fiscal LIKE '%$palavra_chave%' OR  
                            tab_grupos_financeiros.descricao LIKE '%$palavra_chave%' 
                        ) AND  
                        tab_contas_pagar_receber.id_usuario_sistema = :ID_PROPRIETARIO 
                    ORDER BY tab_parcelas.data_vencimento ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Nenhuma Movimentação foi localizada!", 200);

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            $query_grupos_filtros = 
                            "SELECT
                                tab_grupos_financeiros.id_grupo_financeiro as ID_GRUPO,
                                tab_grupos_financeiros.descricao as NOME_GRUPO
                            FROM tab_grupos_financeiros
                            WHERE 
                            tab_grupos_financeiros.id_usuario_sistema = :ID_PROPRIETARIO
                            ORDER BY tab_grupos_financeiros.descricao ASC";

            $res = $pdo->prepare($query_grupos_filtros);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();
            
            $dados_grupos = $res->fetchAll(PDO::FETCH_ASSOC);
            
            
            $contas_pagar_vencer   = 0;
            $contas_pagar_vencidas = 0;
            $contas_pagar_pagas    = 0;
            
            $contas_receber_vencer   = 0;
            $contas_receber_vencidas = 0;
            $contas_receber_pagas    = 0;

            foreach ($dados as $key => $value) {



                switch ($value['ID_TIPO_FIANCEIRO']) {
                    
                    case 91: // Som as Contas a Pagar
                        $contas_pagar_vencer = strpos($value['DESCRICAO_SITUACAO_FINANCEIRO'],'VENCER') === 2 
                        ? $contas_pagar_vencer += $value['VALOR_TOTAL_FINANCEIRO'] 
                        : $contas_pagar_vencer;

                        $contas_pagar_vencidas = strpos($value['DESCRICAO_SITUACAO_FINANCEIRO'],'VENCIDO') === 0 
                        ? $contas_pagar_vencidas += $value['VALOR_TOTAL_FINANCEIRO'] 
                        : $contas_pagar_vencidas;

                        $contas_pagar_pagas = strpos($value['DESCRICAO_SITUACAO_FINANCEIRO'],'PAGO') === 0
                        ? $contas_pagar_pagas += $value['VALOR_TOTAL_FINANCEIRO'] 
                        : $contas_pagar_pagas;
                        
                        break;
                    case 92: // Som as Contas a Pagar
                        
                        $contas_receber_vencer = strpos($value['DESCRICAO_SITUACAO_FINANCEIRO'],'VENCER') === 2
                        ? $contas_receber_vencer += $value['VALOR_TOTAL_FINANCEIRO'] 
                        : $contas_receber_vencer;

                        $contas_receber_vencidas = strpos($value['DESCRICAO_SITUACAO_FINANCEIRO'],'VENCIDO') === 0
                        ? $contas_receber_vencidas += $value['VALOR_TOTAL_FINANCEIRO'] 
                        : $contas_receber_vencidas;

                        $contas_receber_pagas = strpos($value['DESCRICAO_SITUACAO_FINANCEIRO'],'PAGO') === 0
                        ? $contas_receber_pagas += $value['VALOR_TOTAL_FINANCEIRO'] 
                        : $contas_receber_pagas;
                        
                        break;
                }

                $dados[$key]['VALOR_ORIGINAL_FINANCEIRO'] = "R$ " . number_format($value['VALOR_ORIGINAL_FINANCEIRO'],2,',','.');
                $dados[$key]['VALOR_DESCONTO_FINANCEIRO'] = "R$ " . number_format($value['VALOR_DESCONTO_FINANCEIRO'],2,',','.');
                $dados[$key]['VALOR_JUROS_MULTA_FINANCEIRO'] = "R$ " . number_format($value['VALOR_JUROS_MULTA_FINANCEIRO'],2,',','.');
                $dados[$key]['VALOR_TOTAL_FINANCEIRO'] = "R$ " . number_format($value['VALOR_TOTAL_FINANCEIRO'],2,',','.');
                
                $dados[$key]['CONTADOR'] =  $key+1;
            }
            $somatorio = [
                "TOTAL_GERAL_TRANSACOES" => (int)$key + 1,
                "TOTAL_CONTAS_PAGAR_VENCER" => "R$ " . number_format($contas_pagar_vencer,2,',','.'),
                "TOTAL_CONTAS_PAGAR_VENCIDAS" => "R$ " . number_format($contas_pagar_vencidas,2,',','.'),
                "TOTAL_CONTAS_PAGAR_PAGAS" => "R$ " . number_format($contas_pagar_pagas,2,',','.'),               
                "TOTAL_CONTAS_PAGAR" => "R$ " . number_format($contas_pagar_vencer + $contas_pagar_vencidas + $contas_pagar_pagas,2,',','.'),
                "TOTAL_CONTAS_RECEBER_VENCER" => "R$ " . number_format($contas_receber_vencer,2,',','.'),
                "TOTAL_CONTAS_RECEBER_VENCIDAS" => "R$ " . number_format($contas_receber_vencidas,2,',','.'),
                "TOTAL_CONTAS_RECEBER_PAGAS" => "R$ " . number_format($contas_receber_pagas,2,',','.'),               
                "TOTAL_CONTAS_RECEBER" => "R$ " . number_format($contas_receber_vencer + $contas_receber_vencidas + $contas_receber_pagas,2,',','.'),
                "PERIODO_DATA_INICIAL" => $data_inicial,
                "PERIODO_DATA_FINAL" => $data_final,
                "SALDO_PERIODO" => "R$ " . number_format(($contas_receber_vencer + $contas_receber_vencidas + $contas_receber_pagas) - ($contas_pagar_vencer + $contas_pagar_vencidas + $contas_pagar_pagas),2,',','.')            
            ];

            array_push($dados_grupos, ["ID_GRUPO" => "0", "NOME_GRUPO" => "Todos"]);
            array_push($dados, ["grupos" => $dados_grupos]);
            
            
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }
    public function detalhes_financeiro_conta_pagar_receber(ServerRequestInterface $request)
    {
        $params              = (array)$request->getParsedBody();
        $id_proprietario     = $params['id_proprietario'];
        $id_financeiro       = $params['id_financeiro'];

        if (!$id_proprietario || trim($id_financeiro) == "" || !$id_financeiro) return erro("Transação Financeira ou Proprietário com identificação incorreta!");

        try {

            $query_sql = 
                        "SELECT  
                        tab_parcelas.id_parcela as ID_FINANCEIRO, 
                        DATE_FORMAT(tab_contas_pagar_receber.data_competencia, '%d/%m/%Y') as DATA_COMPETENCIA_FINANCEIRO, 
                        UPPER(tab_tipos_transacoes.descricao) as TIPO_TRANSACAO_FINANCEIRO, 
                        tab_contas_pagar_receber.descricao as DESCRICAO_TRANSACAO_FINANCEIRO, 
                        tab_pessoas.nome_razao_social as CLIENTE_FORNECEDOR_FINANCEIRO, 
                        IF(ISNULL(tab_contas_pagar_receber.nota_fiscal) OR TRIM(tab_contas_pagar_receber.nota_fiscal) = '','NÃO INFORMADA',tab_contas_pagar_receber.nota_fiscal) as NOTA_FISCAL_FINANCEIRO, 
                        tab_parcelas.sequencia_parcela as NUMERO_PARCELA_FINANCEIRO, 
                        CONCAT('R$ ',FORMAT(tab_parcelas.valor_original, 2, 'de_DE')) as VALOR_ORIGINAL_FINANCEIRO, 
                        DATE_FORMAT(tab_parcelas.data_vencimento, '%d/%m/%Y') as DATA_VENCIMENTO_FINANCEIRO, 
                        CASE  
                            WHEN tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento >= CURDATE() THEN 'A VENCER'  
                            WHEN tab_parcelas.id_situacao = '49' AND tab_parcelas.data_vencimento < CURDATE() THEN CONCAT('VENCIDO HÁ ',DATEDIFF(CURDATE(),tab_parcelas.data_vencimento), ' Dia(s)')  
                            WHEN tab_parcelas.id_situacao = '50' AND tab_parcelas.data_vencimento >= tab_parcelas.data_pagamento THEN 'PAGO'  
                            WHEN tab_parcelas.id_situacao = '50' AND tab_parcelas.data_vencimento < tab_parcelas.data_pagamento THEN CONCAT('PAGO EM ATRASO - ',DATEDIFF(tab_parcelas.data_pagamento, tab_parcelas.data_vencimento), ' Dia(s)')  
                        END as DESCRICAO_SITUACAO_FINANCEIRO,  
                        IF(tab_parcelas.id_situacao = '50',DATE_FORMAT(tab_parcelas.data_pagamento, '%d/%m/%Y'),'NÃO EFETUADO') as DATA_PAGAMENTO_FINANCEIRO, 
                        UPPER(tab_tipos_meios_pagamentos.descricao) as MEIO_PAGAMENTO_FINANCEIRO, 
                        CONCAT('R$ ',FORMAT(tab_parcelas.valor_desconto, 2, 'de_DE')) as VALOR_DESCONTO_FINANCEIRO, 
                        CONCAT('R$ ',FORMAT(tab_parcelas.valor_multa_juros, 2, 'de_DE')) as VALOR_JUROS_MULTA_FINANCEIRO, 
                        CONCAT('R$ ',FORMAT((tab_parcelas.valor_original - tab_parcelas.valor_desconto + tab_parcelas.valor_multa_juros), 2, 'de_DE')) AS VALOR_TOTAL_FINANCEIRO, 
                        tab_grupos_financeiros.descricao AS GRUPO_FINANCEIRO_FINANCEIRO, 
                        tab_contas.descricao AS CONTA_BANCARIA_FINANCEIRO,
                        IF(ISNULL(tab_parcelas.informacoes_diversas) OR TRIM(tab_parcelas.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_parcelas.informacoes_diversas) as INFORMACAO_FINANCEIRO
                    FROM tab_parcelas  
                        JOIN tab_centros_custos_financeiro ON tab_centros_custos_financeiro.id_parcela = tab_parcelas.id_parcela
                                      JOIN tab_contas_pagar_receber ON tab_contas_pagar_receber.id_conta_pagar_receber = tab_parcelas.id_conta_pagar_receber	 
                        JOIN tab_grupos_financeiros ON tab_grupos_financeiros.id_grupo_financeiro = tab_centros_custos_financeiro.id_centro_custo   
                        JOIN tab_tipos_transacoes ON tab_tipos_transacoes.id_tipo_transacao = tab_grupos_financeiros.id_tipo_grupo_financeiro  
                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_contas_pagar_receber.id_cliente_fornecedor  
                        JOIN tab_tipos_meios_pagamentos ON tab_tipos_meios_pagamentos.id_meio_pagamento = tab_parcelas.id_forma_pagamento  
                        JOIN tab_contas ON tab_contas.id_conta = tab_parcelas.id_conta  
                    WHERE    
                        tab_parcelas.id_parcela = :ID_FINANCEIRO AND tab_contas_pagar_receber.id_usuario_sistema = :ID_PROPRIETARIO";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_FINANCEIRO', $id_financeiro);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Transação Financeira ou Proprietário com identificação incorreta!", 200);

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
          
            return sucesso("", ["dados"=>$dados]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }

    public function listar_grupos_financeiros(ServerRequestInterface $request)
    {
        $params             = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $palavra_chave      = $params['palavra_chave'];
        $data_inicial       = $params['data_inicial'];
        $data_final         = $params['data_final'];
        $tipo_grupo         = $params['tipo_grupo'];

        if (!$id_proprietario || !$tipo_grupo) return erro("Parâmetros inválidos ou faltantes!");

        try {

            // Define o Tipo de Movimentação
            $filtro_tipo_grupo = (int)$tipo_grupo == 1 ? "" : "";
            $filtro_tipo_grupo = (int)$tipo_grupo == 2 ? " tab_grupos_financeiros.id_tipo_grupo_financeiro = '1' AND " : $filtro_tipo_grupo;
            $filtro_tipo_grupo = (int)$tipo_grupo == 3 ? " tab_grupos_financeiros.id_tipo_grupo_financeiro = '2' AND " : $filtro_tipo_grupo;
  
            $query_sql = 
                        "SELECT  
                        tab_grupos_financeiros.id_grupo_financeiro as ID_GRUPO, 
                        UPPER(tab_tipos_transacoes.descricao) as TIPO_GRUPO, 
                        UPPER(tab_grupos_financeiros.descricao) as DESCRICAO_GRUPO, 
                        CONCAT(
                            IF(tab_contas_pagar_receber.id_tipo_transacao = '91',
                            SUM(IF(ISNULL(tab_parcelas.valor_original),0,tab_parcelas.valor_original) - 
                                        IF(ISNULL(tab_parcelas.valor_desconto),0,tab_parcelas.valor_desconto) + 
                                            IF(ISNULL(tab_parcelas.valor_multa_juros),0,tab_parcelas.valor_multa_juros)) * - 1,
                            SUM(IF(ISNULL(tab_parcelas.valor_original),0,tab_parcelas.valor_original) - 
                                        IF(ISNULL(tab_parcelas.valor_desconto),0,tab_parcelas.valor_desconto) + 
                                            IF(ISNULL(tab_parcelas.valor_multa_juros),0,tab_parcelas.valor_multa_juros))
                            )) AS VALOR_TOTAL_GRUPO
                    FROM tab_grupos_financeiros  
                        JOIN tab_tipos_transacoes ON tab_tipos_transacoes.id_tipo_transacao = tab_grupos_financeiros.id_tipo_grupo_financeiro 
                                        LEFT JOIN tab_centros_custos_financeiro ON tab_centros_custos_financeiro.id_centro_custo = tab_grupos_financeiros.id_grupo_financeiro 
                                        LEFT JOIN tab_parcelas ON tab_parcelas.id_parcela = tab_centros_custos_financeiro.id_parcela AND  
                                    tab_parcelas.data_vencimento BETWEEN '$data_inicial' AND '$data_final'
                        LEFT JOIN tab_contas_pagar_receber ON tab_contas_pagar_receber.id_conta_pagar_receber = tab_parcelas.id_conta_pagar_receber AND tab_contas_pagar_receber.id_usuario_sistema = '$id_proprietario' 
                          
                    WHERE  
                    $filtro_tipo_grupo 
                    ( 
                        tab_grupos_financeiros.descricao LIKE '%$palavra_chave%' OR  
                        tab_grupos_financeiros.informacoes_diversas LIKE '%$palavra_chave%' 
                    ) AND  
                    tab_grupos_financeiros.id_usuario_sistema = :ID_PROPRIETARIO 
                    GROUP BY tab_grupos_financeiros.id_grupo_financeiro  
                    ORDER BY tab_grupos_financeiros.descricao ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Transação Financeira ou Proprietário com identificação incorreta!", 200);

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
          
            $valor_grupos_receitas = 0;
            $valor_grupos_despesas = 0;
            foreach ($dados as $key => $value) {
                
                trim($value['TIPO_GRUPO']) == "RECEITA" 
                ? $valor_grupos_receitas = $valor_grupos_receitas + $value['VALOR_TOTAL_GRUPO'] 
                : $valor_grupos_despesas = $valor_grupos_despesas + $value['VALOR_TOTAL_GRUPO'];
                
                $dados[$key]['VALOR_TOTAL_GRUPO'] = "R$ " . number_format($value['VALOR_TOTAL_GRUPO'],2,',','.');
                $dados[$key]['CONTADOR'] =  $key+1;
            }

            $somatorio = [
                "TOTAL_GERAL_GRUPOS" => (int)$key + 1,
                "TOTAL_GRUPOS_RECEITA" => "R$ " . number_format($valor_grupos_receitas,2,',','.'),
                "TOTAL_GRUPOS_DESPESA" => "R$ " . number_format($valor_grupos_despesas,2,',','.'),
                "PERIODO_DATA_INICIAL" => $data_inicial,
                "PERIODO_DATA_FINAL" => $data_final,
                "SALDO_GRUPOS" => "R$ " . number_format($valor_grupos_receitas + $valor_grupos_despesas,2,',','.')
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=>$somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }

}