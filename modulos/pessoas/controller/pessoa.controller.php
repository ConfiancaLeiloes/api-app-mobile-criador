<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PessoaController 
{
	protected $pessoa;
	public function __construct()
	{
		$this->pessoa = new PessoaModel();
	}



	/**
	 * Método proprietarios_criadores()
	 * @author Antonio Ferreira <@toniferreirasantos>
	 * @return function
	*/
	public function proprietarios_criadores(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$res = $this->pessoa->proprietarios_criadores($request);
		$response->getBody()->write($res);
		return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');
	}


	public function teste()
	{
		exit("teste");
	}




}
