<?php
use Psr\Http\Message\ServerRequestInterface;

class SanitarioModel
{
    private $conn;
    public function __construct($conn = null) {
       $this->conn = new ConexaoModel();
    }

    public function listar_sanitario(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = @$params['id_proprietario'];
        $palavra_chave      = @$params['palavra_chave'];
        $data_inicial       = @$params['data_inicial'];
        $data_final         = @$params['data_final'];
        $situacao_controle  = @$params['situacao_controle'];

        if (!@$id_proprietario || !$data_inicial || !$data_final || !$situacao_controle) 
            return erro("Parâmetros inválidos ou faltantes!");
        
        // Define o Grupo
        $filtro_situacao_sanitario = (int)$situacao_controle == 1 ? "" : "" ;
        $filtro_situacao_sanitario = (int)$situacao_controle == 2 ? " tab_controle_sanitario.id_situacao = '32' AND " : "" ;
        $filtro_situacao_sanitario = (int)$situacao_controle == 3 ? " tab_controle_sanitario.id_situacao = '33' AND " : $filtro_situacao_sanitario;
        $filtro_situacao_sanitario = (int)$situacao_controle == 4 ? " tab_controle_sanitario.id_situacao = '87' AND " : $filtro_situacao_sanitario;

        try {

            $query_sql = 
                        "SELECT  
                        tab_controle_sanitario.id_manejo as ID_SANITARIO,  
                        tab_controle_sanitario.descricao as DESCRICAO_SANITARIO, 
                        DATE_FORMAT(tab_controle_sanitario.data_inicio, '%d/%m/%Y') as DATA_SANITARIO,
                        tab_controle_sanitario.id_situacao as ID_SITUACAO_SANITARIO,
                        UPPER(tab_situacoes.descricao) as SITUACAO_SANITARIO,
                        UPPER(tab_pessoas.nome_razao_social) as RESPONSAVEL_SANITARIO,
                        COUNT(tab_animais_manejo.id_animal_manejo) as QUANTIDADE_ANIMAIS_SANITARIO,
                        tab_controle_sanitario.informacoes_diversas as DETALHES_SANITARIO
                    FROM tab_controle_sanitario
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_controle_sanitario.id_situacao
                        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_controle_sanitario.id_veterinario_colaborador
                        JOIN tab_animais_manejo ON tab_animais_manejo.id_manejo = tab_controle_sanitario.id_manejo
                        JOIN tab_animais ON tab_animais.id_animal = tab_animais_manejo.id_animal
                    WHERE
                        $filtro_situacao_sanitario
                        tab_controle_sanitario.data_inicio BETWEEN '$data_inicial' AND '$data_final' AND
                        ( 
                            tab_animais.nome LIKE '%$palavra_chave%' OR  
                            tab_animais.marca LIKE '%$palavra_chave%' OR  
                            tab_animais.registro_associacao LIKE '%$palavra_chave%' OR    
                            tab_animais.chip LIKE '%$palavra_chave%' OR    
                            tab_controle_sanitario.descricao LIKE '%$palavra_chave%' OR
                            tab_pessoas.nome_razao_social LIKE '%$palavra_chave%'
                        ) AND 
                        tab_controle_sanitario.id_usuario_sistema = :ID_PROPRIETARIO
                    GROUP BY tab_controle_sanitario.id_manejo 
                    ORDER BY tab_controle_sanitario.data_inicio ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();            
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($dados) <= 0) return erro("Nenhum Controle Sanitário foi localizado!", 200);
            
            $situacao_agendado  = 0;
            $situacao_executado = 0;
            $situacao_cancelado = 0;
            foreach ($dados as $key => $value) {
                     // Soma as Situações dos Exames
                     $situacao_agendado = (int)$value['ID_SITUACAO_SANITARIO'] == "32" ? $situacao_agendado+1 : $situacao_agendado ;
                     $situacao_executado = (int)$value['ID_SITUACAO_SANITARIO'] == "33" ? $situacao_executado+1 :  $situacao_executado;
                     $situacao_cancelado = (int)$value['ID_SITUACAO_SANITARIO'] == "87" ? $situacao_cancelado+1 :  $situacao_cancelado;

                     $dados[$key]['CONTADOR'] =  $key+1;
            }
            $somatorio = [
                "TOTAL_GERAL_EXAMES" => (int)$key+1,
                "SITUACAO_AGENDADO" => (int)$situacao_agendado,
                "SITUACAO_EXECUTADO" => (int)$situacao_executado,
                "SITUACAO_CANCELADO" => (int)$situacao_cancelado               
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }
        
    }

    public function listar_exames(ServerRequestInterface $request)
    {
        $params = (array)$request->getParsedBody();
        $id_proprietario    = @$params['id_proprietario'];
        $palavra_chave      = @$params['palavra_chave'];
        $data_inicial       = @$params['data_inicial'];
        $data_final         = @$params['data_final'];
        $resultado_exame  = @$params['resultado_exame'];

        if (!@$id_proprietario || !$data_inicial || !$data_final || ! $resultado_exame) 
            return erro("Parâmetros inválidos ou faltantes!");
        
        // Define o Grupo
        $filtro_resultado = (int)$resultado_exame == 1 ? "" : "" ;
        $filtro_resultado = (int)$resultado_exame == 2 ? " tab_exames.id_resultado = '81' AND " : $filtro_resultado;
        $filtro_resultado = (int)$resultado_exame == 3 ? " tab_exames.id_resultado = '82' AND " : $filtro_resultado;
        $filtro_resultado = (int)$resultado_exame == 4 ? " tab_exames.id_resultado = '83' AND " : $filtro_resultado;
        $filtro_resultado = (int)$resultado_exame == 5 ? " tab_exames.id_resultado = '84' AND " : $filtro_resultado;
        $filtro_resultado = (int)$resultado_exame == 6 ? " tab_exames.id_resultado = '85' AND " : $filtro_resultado;
        $filtro_resultado = (int)$resultado_exame == 7 ? " tab_exames.id_resultado = '86' AND " : $filtro_resultado;

        try {

            $query_sql = 
                        "SELECT  
                        tab_exames.id_exame as ID_EXAME,
                        UPPER(tab_tipos_exames.nome_exame) as TIPO_EXAME,
                        UPPER(tab_animais.nome) as ANIMAL_EXAME,
                        IF(ISNULL(tab_laboratorio.nome_razao_social),'NÃO INFORMADO',CONCAT(UPPER(tab_laboratorio.nome_razao_social),'\nTelefone: ',IF(ISNULL(tab_laboratorio.telefone_celular) OR TRIM(tab_laboratorio.telefone_celular) = '','SEM NÚMERO',tab_laboratorio.telefone_celular), '\nE-mail: ',IF(ISNULL(tab_laboratorio.email_usuario) OR TRIM(tab_laboratorio.email_usuario) = '','SEM E-MAIL',tab_laboratorio.email_usuario))) as LABORATORIO_EXAME,
                        tab_exames.id_resultado as ID_RESULTADO_EXAME,
                        UPPER(tab_resultados.descricao) as RESULTADO_EXAME,
                        DATE_FORMAT(tab_exames.data_resultado, '%d/%m/%Y') as DATA_RESULTADO_EXAME, 
                        DATE_FORMAT(tab_exames.data_validade, '%d/%m/%Y') as DATA_VALIDADE_EXAME
                    FROM tab_exames 
                        JOIN tab_animais ON tab_animais.id_animal = tab_exames.id_animal
                        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_exames.id_situacao
                        JOIN tab_situacoes as tab_resultados ON tab_resultados.id_situacao = tab_exames.id_resultado
                        JOIN tab_tipos_exames ON tab_tipos_exames.id_tipo_exame = tab_exames.id_tipo_exame
                        JOIN tab_pessoas as tab_laboratorio ON tab_laboratorio.id_pessoa = tab_exames.id_laboratorio   
                    WHERE 
                        $filtro_resultado
                        tab_exames.data_resultado BETWEEN '$data_inicial' AND '$data_final' AND
                        (
                            tab_animais.nome LIKE '%$palavra_chave%' OR
                            tab_exames.informacoes_diversas LIKE '%$palavra_chave%' OR
                            tab_resultados.descricao LIKE '%$palavra_chave%' OR
                            tab_tipos_exames.nome_exame LIKE '%$palavra_chave%' OR
                            tab_tipos_exames.descricao_material LIKE '%$palavra_chave%' OR
                            tab_laboratorio.nome_razao_social LIKE '%$palavra_chave%'
                        ) AND                
                        tab_exames.id_usuario_sistema = :ID_PROPRIETARIO AND
                        tab_exames.id_situacao = '1'
                    GROUP BY tab_exames.id_exame 
                    ORDER BY tab_exames.data_resultado ASC";

            $pdo = $this->conn->conectar();
            $res = $pdo->prepare($query_sql);
            $res->bindValue(':ID_PROPRIETARIO', $id_proprietario);
            $res->execute();            
            $dados = $res->fetchAll(PDO::FETCH_ASSOC);
                
            if (count($dados) <= 0) return erro("Nenhum Exame foi localizado!", 200);
            
            $resultado_positivo      = 0;
            $resultado_negativo      = 0;
            $resultado_alterado      = 0;
            $resultado_indeterminado = 0;
            $resultado_normal        = 0;
            $resultado_aguardando    = 0;
            foreach ($dados as $key => $value) {

                     // Soma as Situações dos Exames
                     $resultado_positivo = (int)$value['ID_RESULTADO_EXAME']        == "81" ? $resultado_positivo+1 : $resultado_positivo ;
                     $resultado_negativo = (int)$value['ID_RESULTADO_EXAME']        == "82" ? $resultado_negativo+1 : $resultado_negativo;
                     $resultado_alterado = (int)$value['ID_RESULTADO_EXAME']        == "83" ? $resultado_alterado+1 : $resultado_alterado;
                     $resultado_indeterminado = (int)$value['ID_RESULTADO_EXAME']   == "84" ? $resultado_indeterminado+1 : $resultado_indeterminado;
                     $resultado_normal = (int)$value['ID_RESULTADO_EXAME']          == "85" ? $resultado_normal+1 : $resultado_normal;
                     $resultado_aguardando = (int)$value['ID_RESULTADO_EXAME']      == "86" ? $resultado_aguardando+1 : $resultado_aguardando;

                     $dados[$key]['CONTADOR'] = $key+1;
            }
            $somatorio = [
                "TOTAL_GERAL_EXAMES" => (int)$key+1,
                "RESULTADO_POSITIVO" => (int)$resultado_positivo,
                "RESULTADO_NEGATIVO" => (int)$resultado_negativo,
                "RESULTADO_ALTERADO" => (int)$resultado_alterado,
                "RESULTADO_INDETERMINADO" => (int)$resultado_indeterminado,
                "RESULTADO_NORMAL" => (int)$resultado_normal,
                "RESULTADO_AGUARDANDO" => (int)$resultado_aguardando           
            ];
            return sucesso("", ["dados"=>$dados, "resumo"=> $somatorio]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), (int)$th->getCode());
        }
        
    }

    
}