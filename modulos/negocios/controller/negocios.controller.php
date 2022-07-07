<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class NegociosController 
{
    private $negocios;
    public function __construct($negocios) 
    {
       $this->negocios = new NegociosModel();
    }

    public function listar_negocios(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->negocios->listar_negocios($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
    }
    public function listar_clientes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->negocios->listar_clientes($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
    }
    public function detalhes_negocio_compra_venda(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->negocios->detalhes_negocio_compra_venda($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
    }
   
}
  