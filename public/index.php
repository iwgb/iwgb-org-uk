<?php

use Iwgb\OrgUk\Handler;
use Iwgb\OrgUk\Intl\IntlMiddleware;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Middlewares\TrailingSlash;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;
use Teapot\StatusCode;

$c = require '../bootstrap.php';

$app = AppFactory::createFromContainer($c);

$app->add(new TrailingSlash(false));
$app->add(new ContentLengthMiddleware());
$app->add(new IntlMiddleware(
    $c->get('intl'),
    $c->get('settings')['languages'],
    $c->get('session'))
);

$app->group('', function (RouteCollectorProxy $app): void {

    $app->get("/assets/{type}/{file:[A-z0-9\/_-]+}.{ext}", Handler\AssetProxy::class);

    $app->post('/callback/ghost/rebuild', Handler\PurgeCmsCache::class);

    $app->get('/page/covid-19', fn(Request $request, Response $response, array $args) =>
        Psr7::redirect($response, '/covid-19', StatusCode::MOVED_PERMANENTLY));
    $app->get('/page/info/coronavirus', fn(Request $request, Response $response, array $args) =>
        Psr7::redirect($response, '/covid-19', StatusCode::MOVED_PERMANENTLY));
    $app->get('/donate', fn(Request $request, Response $response, array $args) =>
        Psr7::redirect($response, '/page/donate', StatusCode::MOVED_PERMANENTLY));
    $app->get('/page/{subcategory}/{page}', fn(Request $request, Response $response, array $args) =>
        Psr7::redirect($response, "/page/{$args['page']}", StatusCode::FOUND));
});

$app->group('', function (RouteCollectorProxy $app): void {
    $app->get('/', Handler\Home::class);

    $app->group('/post', function (RouteCollectorProxy $app): void {

        $app->get('/{id}/{slug}', Handler\LegacyPost::class);
        $app->get('/{slug}', Handler\Post::class);
    });

    $app->get('/page/{slug}', Handler\Page::class);

    $app->group('/feed', function (RouteCollectorProxy $app): void {

        $app->get('/{tag}/{page}', Handler\Feed::class);
        $app->get('/{tag}', Handler\Feed::class);
    });

    $app->group('/join', function (RouteCollectorProxy $app): void {

        $app->get( '', Handler\Join::class);
        $app->get('/{jobType}', Handler\RedirectToJobType::class);
    });

    $app->get('/error', Handler\Error::class);

    $app->post('/contact', Handler\Contact::class);

    $app->get('/covid-19[/{page}]', Handler\CovidPage::class);
});

$app->run();
