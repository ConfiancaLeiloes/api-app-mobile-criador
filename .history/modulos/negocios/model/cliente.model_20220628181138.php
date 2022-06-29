<?php
use Psr\Http\Message\ServerRequestInterface;
class ClienteController
{
  private $conn;

  public function __construct($conn = null) {
    $this->conn = new ConexaoModel();
  }

  /**
   * Método index
   * @author Antonio Ferreira
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
