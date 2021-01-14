<?php

use DI\Container;
use Iwgb\OrgUk\CorsMiddleware;
use Iwgb\OrgUk\Handler;
use Iwgb\OrgUk\Intl\IntlApiAuthMiddleware;
use Iwgb\OrgUk\Provider\Provider;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy as Group;
use Teapot\StatusCode;

return function (App $app, Container $c): void {
    $app->group('', function (Group $app): void {

        $app->get("/assets/{type}/{file:[A-z0-9\/_-]+}.{ext}", Handler\AssetProxy::class);

        $app->group('/callback/ghost/rebuild', function (Group $app): void {
            $app->post('', Handler\PurgeCmsCache::class);
            $app->options('', fn (Request $request, Response $response, array $args) =>
                $response->withStatus(StatusCode::NO_CONTENT));
        })->add(CorsMiddleware::withOptions());

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
    });

    $app->options('/intl-api/{langpack}[/{language}/{method}]', fn (Request $request, Response $response, array $args) =>
        $response->withStatus(StatusCode::NO_CONTENT)
    )->add(CorsMiddleware::withOptions(['credentials' => true]));

    $app->group('/intl-api', function (Group $app): void {
        $app->get('/all', Handler\Intl\AllLangpacks::class);
        $app->get('/{langpack}/{language}/get', Handler\Intl\GetLangpack::class);
        $app->get('/{langpack}/{language}/put', Handler\Intl\PutLangpack::class);
    })->add(new IntlApiAuthMiddleware($c->get(Provider::SETTINGS)['intlApiKey']))
        ->add(CorsMiddleware::withOptions(['credentials' => true]));
};
