<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClienteController 
{
	private $cliente;
	public function __construct($cliente)
	{
		$this->cliente = new ClienteModel();
	}

	public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{

		echo '<pre>';
			// var_dump($request->getQueryParams());
			var_dump($args);
		echo '</pre>';

		exit;
		$resultado = $this->animais->index();
		$response->getBody()->write($resultado);
		return $response->withStatus(200)->withHeader('Content-type', 'application/json');	 
	}

	public function teste1() {
		exit("Rodando ::teste1()");
	}

	public function teste2() {
		exit("Rodando ::teste2()");
	}


	public function teste3() {
		exit("Rodando ::teste2()");
	}


}
