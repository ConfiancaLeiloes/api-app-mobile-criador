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

    public function teste1(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        exit("Rodando ::teste1()");
    }

    public function teste2(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        // // var_dump(['ok ok']); exit;
        // var_dump($request->getQueryParams());
        // exit;

        exit("Rodando ::teste2()");

        echo parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); exit;

        $resultado = $this->cliente->index();
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }

}
