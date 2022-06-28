<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

header('Access-Control-Allow-Origin:*'); 
header('Access-Control-Allow-Headers:X-Request-With');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

require_once 'loads.php';
require __DIR__ . '/vendor/autoload.php';



$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->setBasePath("/confianca/api/api-app-mobile-criador/apis/plantel"); // PRECISA SETAR CADA CAMINHO 


// The RoutingMiddleware should be added after our CORS middleware so routing is performed first
$app->get('/', PlantelController::class . ':index');

try {
    $app->run();
} catch (\Throwable $th) {
    //print_r($app->run());
    exit(json_encode(["resposta" => "Erro, página não encontrada.."]));
}