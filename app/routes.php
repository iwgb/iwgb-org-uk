<?php

use Iwgb\OrgUk\Handler;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy as Group;
use Teapot\StatusCode;
use Tuupola\Middleware\CorsMiddleware;

$cors = new CorsMiddleware([
    'origin' => ['*'],
    'methods' => ['POST', 'OPTIONS'],
]);

return function (App $app) use ($cors): void {

    $app->group('', function (Group $app) use ($cors): void {

        $app->get("/assets/{type}/{file:[A-z0-9\/_-]+}.{ext}", Handler\AssetProxy::class);

        $app->group('/callback/ghost/rebuild', function (Group $app) use ($cors): void {
            $app->post('', Handler\PurgeCmsCache::class);
            $app->options('', fn (Request $request, Response $response, array $args) =>
                $response->withStatus(StatusCode::NO_CONTENT));
        })->add($cors);

        $app->get('/page/covid-19', fn(Request $request, Response $response, array $args) =>
            Psr7::redirect($response, '/covid-19', StatusCode::MOVED_PERMANENTLY));
        $app->get('/page/info/coronavirus', fn(Request $request, Response $response, array $args) =>
            Psr7::redirect($response, '/covid-19', StatusCode::MOVED_PERMANENTLY));
        $app->get('/donate', fn(Request $request, Response $response, array $args) =>
            Psr7::redirect($response, '/page/donate', StatusCode::MOVED_PERMANENTLY));
        $app->get('/page/{subcategory}/{page}', fn(Request $request, Response $response, array $args) =>
            Psr7::redirect($response, "/page/{$args['page']}", StatusCode::FOUND));
        $app->get('/post/iwgb-charity-workers-branch-statement-on-covid-19', fn(Request $request, Response $response, array $args) =>
            Psr7::redirect($response, '/page/iwgb-charity-workers-branch-covid-19-statement', StatusCode::MOVED_PERMANENTLY));
    });

    $app->group('', function (Group $app): void {
        $app->get('/', Handler\Home::class);

        $app->group('/post', function (Group $app): void {

            $app->get('/{id}/{slug}', Handler\LegacyPost::class);
            $app->get('/{slug}', Handler\Post::class);
        });

        $app->get('/page/{slug}', Handler\Page::class);

        $app->get('/press-releases[/{page}]', Handler\PressReleases::class);

        $app->group('/join', function (Group $app): void {

            $app->get( '', Handler\Join::class);
            $app->get('/{jobType}', Handler\RedirectToJobType::class);
        });

        $app->get('/error', Handler\Error::class);
        $app->get('/404', Handler\NotFound::class);

        $app->post('/contact', Handler\Contact::class);

        $app->get('/covid-19[/{page}]', Handler\CovidPage::class);
    });
};
