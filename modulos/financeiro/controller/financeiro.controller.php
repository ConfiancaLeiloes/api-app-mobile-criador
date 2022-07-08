<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class FinanceiroController 
{
    private $financeiro;
    public function __construct($financeiro) 
    {
       $this->financeiro = new FinanceiroModel();
    }

    public function listar_contas_pagar_receber(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->financeiro->listar_contas_pagar_receber($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
    }
    public function detalhes_financeiro_conta_pagar_receber(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->financeiro->detalhes_financeiro_conta_pagar_receber($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function listar_grupos_financeiros(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->financeiro->listar_grupos_financeiros($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
}
  