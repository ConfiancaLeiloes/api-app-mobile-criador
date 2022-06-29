<?php

use Psr\Http\Message\ServerRequestInterface;

class ClienteModel
{
  private $conn;

  public function __construct($conn = null) {
    $this->conn = new ConexaoModel();
  }

  /**
   * Método index
   * @author Antônio Ferreira <@toniferreirasantos>
   * @return 
  */
  public function index() {  
    $pdo = $this->conn->conectar();
    $res = $pdo->query("SELECT * FROM tab_pessoas LIMIT 10");

    $retorno = $res->fetchAll(PDO::FETCH_OBJ);
    return json_encode(["mensagem" => "Hello confianca", "Conexão com o BD: " => $retorno]);
  }

}
