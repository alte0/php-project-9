<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
/*$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(
        __DIR__ . '/../templates',
        [
            'title' => 'Анализатор страниц'
        ]
    );
});*/
$container->set(Twig::class, function() {
    return Twig::create(
        __DIR__ . '/../templates',
        [
//            'cache' => '/../cache/twig'
            'cache' => false
        ]
    );
});

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->add(TwigMiddleware::create($app, $container->get(Twig::class)));
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response, $args) {
    $viewData = [
        'title' => 'wefwefwe',
    ];

    $twig = $this->get(Twig::class);

    return $twig->render($response, 'index.twig', $viewData);
})->setName('index');

$app->run();
