<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Url;
use App\UrlCheck;
use App\UrlCheckRepository;
use App\UrlValidator;
use App\UrlRepository;
use Carbon\Carbon;
use DiDom\Document;
use GuzzleHttp\Client as ClientGuzzle;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Valitron\Validator;
use Illuminate\Support\Arr;

Validator::lang('ru');

$container = new Container();
$container->set(Twig::class, function () {
    return Twig::create(
        __DIR__ . '/../templates',
        [
            'cache' => false, // '/../cache/twig'
        ]
    );
});
$container->set('flash', function () {
    $storage = [];

    return new Messages($storage);
});
$container->set(\PDO::class, function () {
    $databaseUrl = (array)\parse_url($_ENV['DATABASE_URL']);

//    $dbDrive = $databaseUrl['scheme'] ?? 'pgsql';
    $dbDrive = 'pgsql';
    $username = $databaseUrl['user'] ?? '';
    $password = $databaseUrl['pass'] ?? '';

    $dsnArr = [];

    if (\array_key_exists('path', $databaseUrl) && $databaseUrl['path']) {
        $dsnArr[] = 'dbname=' . ltrim($databaseUrl['path'], '/');
    }

    if (\array_key_exists('host', $databaseUrl) && $databaseUrl['host']) {
        $dsnArr[] = 'host=' . $databaseUrl['host'];
    }

    if (\array_key_exists('port', $databaseUrl) && $databaseUrl['port']) {
        $dsnArr[] = 'port=' . $databaseUrl['port'];
    } else {
        $dsnArr[] = 'port=5432';
    }

    $dsn = $dbDrive . ':' . \implode(';', $dsnArr);

    $conn = new \PDO($dsn, $username, $password);
    $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

    return $conn;
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(TwigMiddleware::create($app, $container->get(Twig::class)));
$app->add(
    function ($request, $next) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->get('flash')->__construct($_SESSION);

        return $next->handle($request);
    }
);
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$routeParser = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) {
    $messages = $this->get('flash')->getMessages();

    $viewData = [
        'urlError' => \array_key_exists('urlError', $messages) ? $messages['urlError'][0] : '',
        'urlValue' => \array_key_exists('urlValue', $messages) ? $messages['urlValue'][0] : '',
    ];

    return $this->get(Twig::class)->render($response, 'index.twig', $viewData);
})->setName('index');

$app->get('/urls/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
    $urlRepository = $this->get(UrlRepository::class);
    $url = $urlRepository->find($id);

    if ($url === null) {
        return $this->get(Twig::class)->render($response->withStatus(404), '404.twig');
    }

    $successMsg = $this->get('flash')->getMessage('success');
    $UrlCheckRepository = $this->get(UrlCheckRepository::class);
    $checks = $UrlCheckRepository->findByUrlId($id);

    $viewData = [
        'successText' => \is_array($successMsg) && count($successMsg) > 0 ? \implode(' ', $successMsg) : '',
        'url' => $url,
        'checks' => $checks,
    ];

    return $this->get(Twig::class)->render($response, 'url.twig', $viewData);
})->setName('urls.show.id');

$app->get('/urls', function (Request $request, Response $response) {
    $urlRepository = $this->get(UrlRepository::class);
    $urls = $urlRepository->getEntitiesWithLastCheck();

    $viewData = [
        'urls' => $urls,
    ];

    return $this->get(Twig::class)->render($response, 'urls.twig', $viewData);
})->setName('urls.show');

$app->post('/urls/{id}/checks', function (Request $request, Response $response, $args) use ($routeParser) {
    $id = (int)$args['id'];
    $urlRedirect = $routeParser->urlFor('urls.show.id', ['id' => (string)$id]);
    $urlRepository = $this->get(UrlRepository::class);
    $url = $urlRepository->find($id);
    $statusCode = null;
    $h1 = null;
    $title = null;
    $description = null;
    $newResponse = $response->withHeader('Location', $urlRedirect)->withStatus(302);

    try {
        $clientGuzzle = new ClientGuzzle();
        $paramsRequest = [
            'timeout' => 2,
            'connect_timeout' => 1.5
        ];
        $responseClient = $clientGuzzle->request('GET', $url->getName(), $paramsRequest);
        $statusCode = $responseClient->getStatusCode();

        if ($statusCode === 200) {
            $html = $responseClient->getBody()->getContents();
            $document = new Document($html);
            unset($html);
            $htmlH1 = $document->find('h1');
            $htmlTitle = $document->find('title');
            $htmlDesc = $document->find('meta[name="description"]');

            if (count($htmlH1) > 0) {
                $h1 = optional($htmlH1[0])->text();
            }

            if (count($htmlTitle) > 0) {
                $title = optional($htmlTitle[0])->text();
            }

            if (count($htmlDesc) > 0) {
                $description = optional($htmlDesc[0])->attr('content');
            }

            unset($document);
        }
    } catch (Exception $e) {
        return $newResponse;
    }

    $createAt = Carbon::now('UTC')->format('Y-m-d H:m:s');
    $urlCheck = UrlCheck::fromArray([$id, $statusCode, $h1, $title, $createAt, $description]);

    $UrlCheckRepository = $this->get(UrlCheckRepository::class);
    $UrlCheckRepository->save($urlCheck);

    if ($urlCheck->getId() > 0) {
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    }

    return $newResponse;
})->setName('urls.checks.id');

$app->post('/urls', function (Request $request, Response $response) use ($routeParser) {
    $paramsForm = (array)$request->getParsedBody();
    $fieldUrlName = 'url.name';
    $urlValue = \trim(Arr::get($paramsForm, $fieldUrlName, ''));
    $urlValidator = new UrlValidator($paramsForm, $fieldUrlName);

    if ($urlValidator->isHaveError()) {
        $unprocessableEntityCode = 422;
        $viewData = [
            'urlError' => $urlValidator->getErrorText(),
            'urlValue' => $urlValue,
        ];
        $newResponse = $response->withStatus($unprocessableEntityCode);

        return $this->get(Twig::class)->render($newResponse, 'index.twig', $viewData);
    }

    $parseUrl = parse_url($urlValue);
    $siteName = $parseUrl['scheme'] . '://' . $parseUrl['host'];
    $createAt = Carbon::now('UTC')->format('Y-m-d H:m:s');

    $urlRepository = $this->get(UrlRepository::class);
    $urlFind = $urlRepository->findByName($siteName);

    if (\get_class((object)$urlFind) == Url::class) {
        $urlId = $urlFind->getId();
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        $url = Url::fromArray([$siteName, $createAt, null, null]);
        $urlRepository->save($url);
        $urlId = $url->getId();
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }

    $urlRedirect = $routeParser->urlFor('urls.show.id', ['id' => (string)$urlId]);

    return $response->withHeader('Location', $urlRedirect)->withStatus(302);
})->setName('urls.store');

$app->run();
