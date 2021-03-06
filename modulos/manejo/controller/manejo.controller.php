<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class ManejoController 
{
    private $manejo;
    public function __construct($manejo) 
    {
       $this->manejo = new ManejoModel();
    }

    public function listar_movimentacoes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $resultado = $this->manejo->listar_movimentacoes($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function listar_locais(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $resultado = $this->manejo->listar_locais($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function listar_lotes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $resultado = $this->manejo->listar_lotes($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
}
  