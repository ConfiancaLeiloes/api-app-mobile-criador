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
	 * Método cadastro
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/	
	public function perfil(ServerRequestInterface $request, ResponseInterface $response) {
		$res = $this->cliente->perfil($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');
	}


	/**
	 * Método cadastro
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/	
	public function cadastro(ServerRequestInterface $request, ResponseInterface $response) {
		$res = $this->cliente->cadastro($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');
	}



}
