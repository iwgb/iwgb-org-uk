<?php

use Iwgb\OrgUk\Handler;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy as Group;
use Teapot\StatusCode;

return function (App $app): void {

    $app->group('', function (Group $app): void {

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

        $app->group('/feed', function (Group $app): void {

            $app->get('/{tag}/{page}', Handler\Feed::class);
            $app->get('/{tag}', Handler\Feed::class);
        });

        $app->group('/join', function (Group $app): void {

            $app->get( '', Handler\Join::class);
            $app->get('/{jobType}', Handler\RedirectToJobType::class);
        });

        $app->get('/error', Handler\Error::class);

        $app->post('/contact', Handler\Contact::class);

        $app->get('/covid-19[/{page}]', Handler\CovidPage::class);
    });
};
