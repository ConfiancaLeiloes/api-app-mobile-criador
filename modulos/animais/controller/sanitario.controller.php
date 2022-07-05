<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class SanitarioController 
{
    private $sanitario;
    public function __construct($sanitario) 
    {
       $this->sanitario = new SanitarioModel();
    }

    public function listar_sanitario(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->sanitario->listar_sanitario($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(json_decode($resultado)->http_status_code)->withHeader('Content-type', 'application/json');
       
    }
    public function listar_exames(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->sanitario->listar_exames($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(json_decode($resultado)->http_status_code)->withHeader('Content-type', 'application/json');
       
    }
   
}
