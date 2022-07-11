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
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function index()
	{  
		$pdo = $this->conn->conectar();
		$res = $pdo->query("SELECT COUNT(*) from tab_pessoas");

		$retorno = $res->fetchAll(PDO::FETCH_ASSOC);
		return json_encode(["mensagem" => "Hello confianca", "Conexão com o BD: " => $retorno]);
	}


}
