<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class AnimaisController 
{
    private $plantel;
    public function __construct($plantel) 
    {
       $this->plantel = new AnimaisModel();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        var_dump($request->getQueryParams());

        exit;
        //$plantel = new PlantelModel();
        $resultado = $this->animais->index();
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
    public function teste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        var_dump("teste rota 3");

        exit;
        //$plantel = new PlantelModel();
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
}
