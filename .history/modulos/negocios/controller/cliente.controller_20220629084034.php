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

    public function teste2(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        // // var_dump(['ok ok']); exit;
        // var_dump($request->getQueryParams());
        // exit;

        $resultado = $this->animais->index();
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }

}
