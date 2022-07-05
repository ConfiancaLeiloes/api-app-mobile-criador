<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class AnimaisController 
{
    private $animais;
    public function __construct($animais) 
    {
       $this->animais = new AnimaisModel();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        var_dump($request->getQueryParams());

        exit;
        $resultado = $this->animais->index();
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_cobricoes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$plantel = new PlantelModel();

        $resultado = $this->animais->detalhes_animal_cobricoes($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_exames(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$plantel = new PlantelModel();

        $resultado = $this->animais->detalhes_animal_exames($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_filhos(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
         $resultado = $this->animais->detalhes_animal_filhos($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_genealogia(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_genealogia($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_manejo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_manejo($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_negocios(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_negocios($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_perfil(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_perfil($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_sanitario(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_sanitario($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function detalhes_animal_socios(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->detalhes_animal_socios($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function listar_plantel(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->listar_plantel($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function listar_racas(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->listar_racas($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }
    public function menu_plantel(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $resultado = $this->animais->menu_plantel($request);
        $response->getBody()->write($resultado);
        return $response->withStatus( json_decode($resultado)->http_status_code )->withHeader('Content-type', 'application/json');
       
    }



    public function cadastro(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $res = $this->animais->cadastro($request);
        $response->getBody()->write($res);
        return $response->withStatus( json_decode($res)->http_status_code )->withHeader('Content-type', 'application/json');
    }
}
