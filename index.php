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

setlocale (LC_ALL, 'pt_BR');
date_default_timezone_set('America/Sao_Paulo');

require_once 'loads.php';

$app = AppFactory::create();
$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $routeContext = RouteContext::fromRequest($request);
    $routingResults = $routeContext->getRoutingResults();
    $methods = $routingResults->getAllowedMethods();
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = $handler->handle($request);

    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $methods));
    $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);

    return $response;
});

// The RoutingMiddleware should be added after our CORS middleware so routing is performed first
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
// Get the default error handler and register my custom error renderer.
$errorHandler = $errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'].$uri;
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
})());

//$app->get('/detalhes-animal-cobricoes', AnimaisController::class . ':detalhes_animal_cobricoes');


if (
    !uri_contem('/usuario/login')
    && !uri_contem('/usuario/cadastro')
    && !uri_contem('/usuario/recuperar_senha')
 ) {
	UsuarioController::valida_token();
}
try {
    # ROTAS DE ANIMAIS
    require_once ('./modulos/animais/rotas.php');

    # ROTAS DE FINANCEIRO
    //require_once ('./modulos/financeiro/rotas.php');

    # ROTAS DE NEGOCIOS
    require_once ('./modulos/negocios/rotas.php');

    # ROTAS DE MANEJOS
    require_once ('./modulos/manejo/rotas.php');

    

    $app->run();
} catch (\Throwable $th) {
    @header("Status: 500 Rota não encontrada");
    @header("Content-type: application/json; charset=utf-8");
    exit(erro("Erro, rota não encontrada..", 500));
}