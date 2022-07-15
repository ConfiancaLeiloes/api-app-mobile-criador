<?php
use Psr\Http\Message\ServerRequestInterface;

class NegociosModel
{
    private $conn;
    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }
    public function listar_negocios(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $palavra_chave      = $params['palavra_chave'];
        $data_inicial       = $params['data_inicial'];
        $data_final         = $params['data_final'];
        $tipo_negocio       = $params['tipo_negocio'];
        $tipo_produto       = $params['tipo_produto'];
        $situacao_financeiro= $params['situacao_financeiro'];    
        $situacao_entrega   = $params['situacao_entrega'];
        $situacao_negocio   = $params['situacao_negocio'];
        
        if (!@$data_inicial || !@$id_proprietario || !@$situacao_negocio || !@$situacao_financeiro || !@$tipo_produto || !@$situacao_entrega || !@$tipo_negocio)
         return erro("Parâmetros inválidos ou faltantes!");

        try {

            // Define o Tipo de Negócio
            $filtro_tipo_negocio = (int)$tipo_negocio == 1 ? "" : "" ;
            $filtro_tipo_negocio = (int)$tipo_negocio == 2 ? " tab_compras_vendas_animais.id_tipo_negocio = '88' AND " : $filtro_tipo_negocio;
            $filtro_tipo_negocio = (int)$tipo_negocio == 3 ? " tab_compras_vendas_animais.id_tipo_negocio = '89' AND " : $filtro_tipo_negocio;
            $filtro_tipo_negocio = (int)$tipo_negocio == 4 ? " tab_compras_vendas_animais.id_tipo_negocio = '90' AND " : $filtro_tipo_negocio;
            $filtro_tipo_negocio = (int)$tipo_negocio == 5 ? " tab_compras_vendas_animais.id_tipo_negocio = '118' AND " : $filtro_tipo_negocio;

            // Define o Tipo de Produto Comprado/Vendido
            $filtro_tipo_produto = (int)$tipo_produto == 1 ? "" : "" ;
            $filtro_tipo_produto = (int)$tipo_produto == 2 ? " tab_compras_vendas_animais.id_tipo_produto = '1' AND " : $filtro_tipo_produto;
            $filtro_tipo_produto = (int)$tipo_produto == 3 ? " tab_compras_vendas_animais.id_tipo_negocio = '2' AND " : $filtro_tipo_produto;
            $filtro_tipo_produto = (int)$tipo_produto == 4 ? " tab_compras_vendas_animais.id_tipo_negocio = '3' AND " : $filtro_tipo_produto;
            $filtro_tipo_produto = (int)$tipo_produto == 5 ? " tab_compras_vendas_animais.id_tipo_negocio = '4' AND " : $filtro_tipo_produto;


            // Define a Situação do Financeiro
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 1 ? "" : "" ;
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 2 ? " NOT tab_compras_vendas_animais.id_financeiro IS NULL AND " : $filtro_situacao_financeiro;
            $filtro_situacao_financeiro = (int)$situacao_financeiro == 3 ? " tab_compras_vendas_animais.id_financeiro IS NULL AND " : $filtro_situacao_financeiro;
            
            // Define a Situação da Entrega/Recebimento
            $filtro_situacao_entrega = (int)$situacao_entrega == 1 ? "" : "" ;
            $filtro_situacao_entrega = (int)$situacao_entrega == 2 ? " tab_compras_vendas_animais.id_situacao_recebimento_entrega = '38' AND " : $filtro_situacao_entrega;
            $filtro_situacao_entrega = (int)$situacao_entrega == 3 ? " tab_compras_vendas_animais.id_situacao_recebimento_entrega = '39' AND " : $filtro_situacao_entrega;
            $filtro_situacao_entrega = (int)$situacao_entrega == 4 ? " tab_compras_vendas_animais.id_situacao_recebimento_entrega = '40' AND " : $filtro_situacao_entrega;
            $filtro_situacao_entrega = (int)$situacao_entrega == 5 ? " tab_compras_vendas_animais.id_situacao_recebimento_entrega = '41' AND " : $filtro_situacao_entrega;

            // Define a Situação do Negócio
            $filtro_situacao_negocio = (int)$situacao_negocio == 1 ? "" : "" ;
            $filtro_situacao_negocio = (int)$situacao_negocio == 2 ? " tab_compras_vendas_animais.id_situacao_negocio = '42' AND " : $filtro_situacao_negocio;
            $filtro_situacao_negocio = (int)$situacao_negocio == 3 ? " tab_compras_vendas_animais.id_situacao_negocio = '43' AND " : $filtro_situacao_negocio;
            $filtro_situacao_negocio = (int)$situacao_negocio == 4 ? " tab_compras_vendas_animais.id_situacao_negocio = '44' AND " : $filtro_situacao_negocio;



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
                        FORMAT(tab_compras_vendas_animais.quantidade_compra_venda, 2, 'de_DE') as QUANTIDADE_NEGOCIO, 
                        CONCAT(FORMAT(tab_compras_vendas_animais.cotas_compra_venda, 2, 'de_DE'),'%') as COTAS_NEGOCIO, 
                        tab_compras_vendas_animais.valor_total as VALOR_TOTAL_NEGOCIO,
                        UPPER(tab_situacao_entrega_recebimento.descricao) as SITUACAO_ENTREGA_RECEBIMENTO_NEGOCIO, 
                        UPPER(tab_situacao_negocio.descricao) as SITUACAO_NEGOCIO,
                        IF(ISNULL(tab_compras_vendas_animais.id_financeiro),'FINANCEIRO NÃO GERADO', 'FINANCEIRO GERADO')as SITUACAO_FINANCEIRO_NEGOCIO
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
                        $filtro_tipo_negocio
                        $filtro_tipo_produto
                        $filtro_situacao_financeiro
                        $filtro_situacao_entrega
                        $filtro_situacao_negocio
                        tab_compras_vendas_animais.data_compra_venda BETWEEN '$data_inicial' AND '$data_final' AND
                        ( 
                            tab_garanhao.nome LIKE '%$palavra_chave%' OR   
                            tab_doadora.nome LIKE '%$palavra_chave%' OR
                            tab_receptora.nome LIKE '%$palavra_chave%' OR
                            tab_receptora.marca LIKE '%$palavra_chave%' OR
                            tab_pessoas.nome_razao_social LIKE '%$palavra_chave%' OR
                            tab_eventos_equestres.nome_evento  LIKE '%$palavra_chave%' OR
                            tab_cobricoes.informacoes_diversas LIKE '%$palavra_chave%'                    
                        ) AND
                        tab_compras_vendas_animais.id_usuario_sistema = :ID_PROPRIETARIO
                    GROUP BY  tab_compras_vendas_animais.id_compra_venda_animal
                    ORDER BY tab_compras_vendas_animais.data_compra_venda ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Nenhum Negócio foi localizado!", 200);

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            $compras_animais        = 0;
            $valor_compra_animais   = 0;
            $compras_coberturas     = 0;
            $valor_compra_coberturas= 0;
            $compras_ovulos         = 0;
            $valor_compra_ovulos    = 0;
            $compras_embrioes       = 0;
            $valor_compra_embrioes  = 0;
            
            $vendas_animais         = 0;
            $valor_venda_animais    = 0;
            $vendas_coberturas      = 0;
            $valor_venda_coberturas = 0;
            $vendas_ovulos          = 0;
            $valor_venda_ovulos     = 0;
            $vendas_embrioes        = 0;
            $valor_venda_embrioes   = 0;

            $doacao_animais         = 0;
            $doacao_coberturas      = 0;
            $doacao_ovulos          = 0;
            $doacao_embrioes        = 0;

            foreach ($dados as $key => $value) {
                // Soma as COMPRAS
                switch ($value['TIPO_NEGOCIO']) {
                    case 'COMPRA':
                        
                        // Soma os Animais
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "ANIMAL")
                        {
                            $compras_animais++;
                            $valor_compra_animais += $value['VALOR_TOTAL_NEGOCIO'];
                        }
                        
                        // Soma as Coberturas
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "COBERTURA")
                        {
                            $compras_coberturas++;
                            $valor_compra_coberturas += $value['VALOR_TOTAL_NEGOCIO'];
                        } 
                        
                        // Soma os Óvulos
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "OVULO")
                        {
                            $compras_ovulos++;
                            $valor_compra_ovulos += $value['VALOR_TOTAL_NEGOCIO'];
                        }
                        
                        // Soma os Embriões
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "EMBRIÃO")
                        {
                            $compras_embrioes++;
                            $valor_compra_embrioes += $value['VALOR_TOTAL_NEGOCIO'];
                        }

                        break;
                    case 'VENDA':

                        // Soma os Animais
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "ANIMAL")
                        {
                            $vendas_animais++;
                            $valor_venda_animais += $value['VALOR_TOTAL_NEGOCIO'];
                        }
                        
                        // Soma as Coberturas
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "COBERTURA")
                        {
                            $vendas_coberturas++;
                            $valor_venda_coberturas += $value['VALOR_TOTAL_NEGOCIO'];
                        } 
                        
                        // Soma os Óvulos
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "OVULO")
                        {
                            $vendas_ovulos++;
                            $valor_venda_ovulos += $value['VALOR_TOTAL_NEGOCIO'];
                        }
                        
                        // Soma os Embriões
                        if (trim($value['TIPO_PRODUTO_NEGOCIO']) == "EMBRIÃO")
                        {
                            $vendas_embrioes++;
                            $valor_venda_embrioes += $value['VALOR_TOTAL_NEGOCIO'];
                        }
                        break;
                    default:
                        
                        // Soma os Animais
                        $doacao_animais = trim($value['TIPO_PRODUTO_NEGOCIO']) == "ANIMAL"    ? $doacao_animais + 1 : $doacao_animais;
                        
                        // Soma as Coberturas
                        $doacao_coberturas = trim($value['TIPO_PRODUTO_NEGOCIO']) == "COBERTURA" ? $doacao_coberturas + 1 : $doacao_coberturas;

                        // Soma os Óvulos
                        $doacao_ovulos = trim($value['TIPO_PRODUTO_NEGOCIO']) == "OVULO" ? $doacao_ovulos + 1 : $doacao_ovulos;

                        // Soma os Embriões
                        $doacao_embrioes = trim($value['TIPO_PRODUTO_NEGOCIO']) == "EMBRIÃO" ? $doacao_embrioes + 1 : $doacao_embrioes;
                        break;
                }
                $dados[$key]['CONTADOR'] =  $key+1;
                
            }
            $somatorio = [
                "TOTAL_GERAL_NEGOCIOS" => (int)$key+1,
                "TOTAL_COMPRAS_ANIMAIS" => (int)$compras_animais,
                "VALOR_TOTAL_COMPRAS_ANIMAIS" => "R$ " . number_format($valor_compra_animais,2,',','.'),
                "TOTAL_COMPRAS_COBERTURAS" => (int)$compras_coberturas,               
                "VALOR_TOTAL_COMPRAS_COBERTURAS" => "R$ " . number_format($valor_compra_coberturas,2,',','.'),
                "TOTAL_COMPRAS_OVULOS" => (int)$compras_ovulos,
                "VALOR_TOTAL_COMPRAS_OVULOS" => "R$ " . number_format($valor_compra_ovulos,2,',','.'),
                "TOTAL_COMPRAS_EMBRIOES" => (int)$compras_embrioes,
                "VALOR_TOTAL_COMPRAS_EMBRIOES" => "R$ " . number_format($valor_compra_embrioes,2,',','.'),
                "VALOR_TOTAL_COMPRAS" => "R$ " . number_format($valor_compra_animais + $valor_compra_coberturas + $valor_compra_ovulos + $valor_compra_embrioes,2,',','.'),
                "TOTAL_VENDAS_ANIMAIS" => (int)$vendas_animais,
                "VALOR_TOTAL_VENDAS_ANIMAIS" => "R$ " . number_format($valor_venda_animais,2,',','.'),
                "TOTAL_VENDAS_COBERTURAS" => (int)$vendas_coberturas,               
                "VALOR_TOTAL_VENDAS_COBERTURAS" => "R$ " . number_format($valor_venda_coberturas,2,',','.'),
                "TOTAL_VENDAS_OVULOS" => (int)$vendas_ovulos,
                "VALOR_TOTAL_VENDAS_OVULOS" => "R$ " . number_format($valor_venda_ovulos,2,',','.'),
                "TOTAL_VENDAS_EMBRIOES" => (int)$vendas_embrioes,
                "VALOR_TOTAL_VENDAS_EMBRIOES" => "R$ " . number_format($valor_venda_embrioes,2,',','.'),
                "TOTAL_DOACOES_ANIMAIS" => (int)$doacao_animais,
                "TOTAL_DOACOES_COBERTURAS" => (int)$doacao_coberturas,
                "TOTAL_DOACOES_EMBRIOES" => (int)$doacao_embrioes,
                "TOTAL_DOACOES_OVULOS" => (int)$doacao_ovulos,
                "TOTAL_DOACOES" => (int)$doacao_animais + (int)$doacao_coberturas + (int)$doacao_embrioes + (int)$doacao_ovulos,
                "VALOR_TOTAL_VENDAS" => "R$ " . number_format($valor_venda_animais + $valor_venda_coberturas + $valor_venda_ovulos + $valor_venda_embrioes,2,',','.'),
                "VALOR_SALDO_NEGOCIOS" => "R$ " . number_format(($valor_venda_animais + $valor_venda_coberturas + $valor_venda_ovulos + $valor_venda_embrioes) - ($valor_compra_animais + $valor_compra_coberturas + $valor_compra_ovulos + $valor_compra_embrioes),2,',','.'),
                "PERIODO_DATA_INICIAL" => $data_inicial,
                "PERIODO_DATA_FINAL" => $data_final
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }
    public function listar_clientes(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $palavra_chave      = $params['palavra_chave'];
        
        if (!@$id_proprietario)
         return erro("Parâmetros inválidos ou faltantes!");

        try {

            $query_sql = 
                        "SELECT  
                        tab_pessoas.id_pessoa as ID_CLIENTE, 
                        tab_pessoas.nome_razao_social as NOME_CLIENTE, 
                        tab_sexos.sexo_pessoa as SEXO_CLIENTE, 
                        tab_pessoas.cpf_cnpj as CPF_CNPJ_CLIENTE, 
                        tab_pessoas.telefone_fixo as TELEFONE_FIXO_CLIENTE, 
                        tab_pessoas.telefone_celular as TELEFONE_CELULAR_CLIENTE, 
                        tab_pessoas.email_usuario as EMAIL_CLIENTE, 
                        tab_pessoas.logradouro as RUA_CLIENTE, 
                        tab_pessoas.numero as NUMERO_CLIENTE, 
                        tab_pessoas.complemento as COMPLEMENTO_CLIENTE, 
                        tab_pessoas.bairro as BAIRRO_CLIENTE, 
                        tab_cidades.nome_cidade as CIDADE_CLIENTE,  
                        tab_estados.sigla_estado as UF_CLIENTE,  
                        tab_pessoas.cep as CEP_CLIENTE, 
                        tab_pessoas.informacoes_diversas as INFORMACAO_CLIENTE,   
                        (
                            SELECT SUM(tab_parcelas.valor_original)  
                            FROM tab_parcelas  
                            JOIN tab_contas_pagar_receber ON tab_contas_pagar_receber.id_conta_pagar_receber = tab_parcelas.id_conta_pagar_receber AND 
                            tab_contas_pagar_receber.id_tipo_transacao = '92' AND tab_parcelas.id_situacao = '49'  
                            WHERE tab_contas_pagar_receber.id_cliente_fornecedor = tab_pessoas.id_pessoa  
                        ) AS VALOR_TOTAL_DEBITO_CLIENTE,  
                        (
                            SELECT SUM(tab_parcelas.valor_original)  
                            FROM tab_parcelas  
                            JOIN tab_contas_pagar_receber ON tab_contas_pagar_receber.id_conta_pagar_receber = tab_parcelas.id_conta_pagar_receber AND 
                            tab_contas_pagar_receber.id_tipo_transacao = '91' AND tab_parcelas.id_situacao = '49'  
                            WHERE tab_contas_pagar_receber.id_cliente_fornecedor = tab_pessoas.id_pessoa  
                        ) AS VALOR_TOTAL_CREDITO_CLIENTE,  
                        (
                            SELECT COUNT(tab_parcelas.id_parcela)  
                            FROM tab_parcelas  
                            JOIN tab_contas_pagar_receber ON tab_contas_pagar_receber.id_conta_pagar_receber = tab_parcelas.id_conta_pagar_receber AND 
                            tab_parcelas.id_situacao = '49' AND CURDATE() > tab_parcelas.data_vencimento  
                            WHERE tab_contas_pagar_receber.id_cliente_fornecedor = tab_pessoas.id_pessoa  
                        ) AS PARCELA_EM_ATRASO  
                    FROM tab_pessoas 
                    JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_pessoas.id_situacao  
                    LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade  
                    LEFT JOIN tab_estados ON tab_estados.id_estado = tab_pessoas.id_estado  
                    JOIN tab_sexos ON tab_sexos.id_sexo = tab_pessoas.id_sexo  
                    WHERE   
                        ( 
                            tab_pessoas.nome_razao_social LIKE '%$palavra_chave%' OR  
                            tab_pessoas.cpf_cnpj LIKE '%$palavra_chave%' OR  
                            tab_pessoas.telefone_fixo LIKE '%$palavra_chave%' OR  
                            tab_pessoas.telefone_celular LIKE '%$palavra_chave%' OR  
                            tab_pessoas.logradouro LIKE '%$palavra_chave%' OR  
                            tab_pessoas.bairro LIKE '%$palavra_chave%' OR  
                            tab_pessoas.email_usuario LIKE '%$palavra_chave%' OR  
                            tab_cidades.nome_cidade LIKE '%$palavra_chave%' 
                        ) AND  
                        tab_pessoas.id_usuario_sistema = :ID_PROPRIETARIO AND
                        NOT tab_pessoas.id_usuario_sistema = tab_pessoas.id_pessoa
                    GROUP BY tab_pessoas.id_pessoa  
                    ORDER BY nome_razao_social ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Nenhum Cliente foi localizado!", 200);

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            $clientes_devedores    = 0;
            $valor_cliente_devedor = 0;
            $clientes_credores     = 0;
            $valor_cliente_credor  = 0;

            foreach ($dados as $key => $value) {

                // Soma os Clientes Devedores
                if ($value['VALOR_TOTAL_DEBITO_CLIENTE'] > 0)
                {
                    $clientes_devedores++;
                    $valor_cliente_devedor += $value['VALOR_TOTAL_DEBITO_CLIENTE'];
                }

                // Soma os Clientes Credores
                if ($value['VALOR_TOTAL_CREDITO_CLIENTE'] > 0)
                {
                    $clientes_credores++;
                    $valor_cliente_credor += $value['VALOR_TOTAL_CREDITO_CLIENTE'];
                }

                $dados[$key]['CONTADOR'] =  $key+1;
                $dados[$key]['VALOR_TOTAL_ACERTO_CLIENTE'] = "R$ " . number_format($value['VALOR_TOTAL_DEBITO_CLIENTE'] - $value['VALOR_TOTAL_CREDITO_CLIENTE'],2,',','.');
                $dados[$key]['VALOR_TOTAL_DEBITO_CLIENTE'] = "R$ " . number_format($value['VALOR_TOTAL_DEBITO_CLIENTE'],2,',','.');
                $dados[$key]['VALOR_TOTAL_CREDITO_CLIENTE'] = "R$ " . number_format($value['VALOR_TOTAL_CREDITO_CLIENTE'],2,',','.');
                
            }
            $somatorio = [
                "TOTAL_GERAL_CLIENTES" => (int)$key+1,
                "CLIENTES_COM_SALDO_DEVEDOR" => (int)$clientes_devedores,
                "CLIENTES_COM_SALDO_CREDOR" => (int) $clientes_credores,
                "VALOR_TOTAL_RECEBER" => "R$ " . number_format($valor_cliente_devedor,2,',','.'),               
                "VALOR_TOTAL_PAGAR" => "R$ " . number_format($valor_cliente_credor,2,',','.'),
                "VALOR_SALDO" => "R$ " . number_format($valor_cliente_devedor - $valor_cliente_credor,2,',','.')
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }
    public function detalhes_negocio_compra_venda(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = $params['id_proprietario'];
        $id_negocio         = $params['id_negocio'];
        
        if (!@$id_proprietario || !@$id_negocio)
         return erro("Negócio ou Proprietário com identificação incorreta!");

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
                        FORMAT(tab_compras_vendas_animais.quantidade_compra_venda, 2, 'de_DE') as QUANTIDADE_NEGOCIO, 
                        CONCAT(FORMAT(tab_compras_vendas_animais.cotas_compra_venda, 2, 'de_DE'),'%') as COTAS_NEGOCIO, 
                        CONCAT('R$ ',FORMAT(tab_compras_vendas_animais.valor_total, 2, 'de_DE')) as VALOR_TOTAL_NEGOCIO,
                        UPPER(tab_situacao_entrega_recebimento.descricao) as SITUACAO_ENTREGA_RECEBIMENTO_NEGOCIO, 
                        UPPER(tab_situacao_negocio.descricao) as SITUACAO_NEGOCIO,
                        IF(ISNULL(tab_compras_vendas_animais.id_financeiro),'FINANCEIRO NÃO GERADO', 'FINANCEIRO GERADO')as SITUACAO_FINANCEIRO_NEGOCIO,
                        tab_compras_vendas_animais.total_parcelas as TOTAL_PARCELAS_NEGOCIO,
                        IF(ISNULL(tab_compras_vendas_animais.composicao_parcelas) OR TRIM(tab_compras_vendas_animais.composicao_parcelas) = '','NÃO INFORMADO',tab_compras_vendas_animais.composicao_parcelas) as COMPOSICAO_PARCELAS_NEGOCIO,
                        CONCAT(CONCAT('R$ ',FORMAT(tab_compras_vendas_animais.valor_total * (tab_compras_vendas_animais.comissao_compra_venda / 100), 2, 'de_DE')), ' (',FORMAT(tab_compras_vendas_animais.comissao_compra_venda, 2, 'de_DE'),'%)') as COMISSAO_NEGOCIO,
                        CONCAT('R$ ',FORMAT(tab_compras_vendas_animais.inscricao_leilao, 2, 'de_DE')) as VALOR_INSCRICAO_NEGOCIO,
                        IF(ISNULL(tab_compras_vendas_animais.informacoes_diversas) OR TRIM(tab_compras_vendas_animais.informacoes_diversas) = '','SEM INFORMAÇÕES ADICIONAIS',tab_compras_vendas_animais.informacoes_diversas) as INFORMACAO_NEGOCIO
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
                        tab_compras_vendas_animais.id_compra_venda_animal = :ID_NEGOCIO AND tab_compras_vendas_animais.id_usuario_sistema = :ID_PROPRIETARIO
                    GROUP BY  tab_compras_vendas_animais.id_compra_venda_animal
                    ORDER BY tab_compras_vendas_animais.data_compra_venda ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_NEGOCIO', $id_negocio);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();  
            
            if ($res->rowCount() <= 0 ) return erro("Negócio ou Proprietário com identificação incorreta!");

            $dados = $res->fetchAll(PDO::FETCH_ASSOC);

            return sucesso("", ["dados"=>$dados]);
        } 
        catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }        
    }

}