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
        //$id_animal = $params['id_animal'];
        //$id_proprietario = $params['id_proprietario'];
        $id_animal = '';
        $id_proprietario = 8301;

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
}
