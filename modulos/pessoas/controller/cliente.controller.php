<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClienteController extends PessoaController
{

	private $cliente;
	public function __construct($cliente)
	{
		$this->cliente = new ClienteModel();
	}


	/**
	 * Método
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return 
	*/
	public function teste2(ServerRequestInterface $request, ResponseInterface $response) {
		$res = $this->cliente->teste2($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');	 
	}


}
