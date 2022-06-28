<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClienteController 
{
  private $cliente;
  
  public function __construct($cliente)  {
    $this->cliente = new ClienteModel();
  }

  public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    $resultado = $this->plantel->index();
    $response->getBody()->write($resultado);
    return $response->withStatus(200)->withHeader('Content-type', 'application/json');    
  }

  public function detalhes_animal_cobricoes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
  
    $resultado = $this->plantel->detalhes_animal_cobricoes($request);
    $response->getBody()->write($resultado);
    return $response->withStatus(200)->withHeader('Content-type', 'application/json');
      
  }
}
