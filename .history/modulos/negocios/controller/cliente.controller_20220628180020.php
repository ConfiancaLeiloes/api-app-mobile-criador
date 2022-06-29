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
   
    $response->['teste'];
    return $response->withStatus(200)->withHeader('Content-type', 'application/json');    
  }

  
}
