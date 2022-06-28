<?php
use Psr\Http\Message\ServerRequestInterface;
class PlantelModel
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
}
