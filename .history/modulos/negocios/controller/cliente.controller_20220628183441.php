<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class ClienteController 
{
    private $animais;
    public function __construct($animais) 
    {
       $this->animais = new ClienteModel();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        var_dump($request->getQueryParams());

        exit;
        $resultado = $this->animais->index();
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function teste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        var_dump("teste rota 3");

        exit;
        $resultado = $this->animais->index();
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_cobricoes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$plantel = new PlantelModel();

        $resultado = $this->animais->detalhes_animal_cobricoes($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_exames(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$plantel = new PlantelModel();

        $resultado = $this->animais->detalhes_animal_exames($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_filhos(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
         $resultado = $this->animais->detalhes_animal_filhos($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_genealogia(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_genealogia($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_manejo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_manejo($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_negocios(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_negocios($request);
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
}
