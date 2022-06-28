<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class PlantelController 
{
    private $plantel;
    public function __construct($plantel) 
    {
       $this->plantel = new PlantelModel();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$plantel = new PlantelModel();


        $resultado = $this->plantel->index();
        $response->getBody()->write($resultado);
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
       
    }
}
