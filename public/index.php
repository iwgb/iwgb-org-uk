<?php

$c = require '../bootstrap.php';

use Iwgb\OrgUk\Handler;
use Pimple\Container;
use Siler\Container as Router;
use Siler\Http\Response;
use Siler\Route as http;
use Iwgb\OrgUk\Intl\IntlUtility;

try {
    dispatch($c);
} catch (Exception $e) {
    _catch($e);
    throw $e;
//    Response\redirect('/error');
}

function dispatch(Container $c) {

    http\get("/assets/{type}/(?'file'[A-z0-9\/_-]+)\.{ext}", new Handler\AssetProxy($c));

    http\get(__($c, ''), new Handler\Home($c));

    http\get(__($c, '/page/covid-19'), fn(array $params) => Response\redirect('/covid-19'));

    http\get(__($c, "/post/{id}/{slug}"), new Handler\LegacyPost($c));
    http\get(__($c, '/post/{slug}'), new Handler\Post($c));
    http\get(__($c, '/page/{slug}'), new Handler\Page($c));

    http\get(__($c, '/feed/{tag}/{page}'), new Handler\Feed($c));
    http\get(__($c, '/feed/{tag}'), new Handler\Feed($c));

    http\get(__($c, '/join'), new Handler\Join($c));
    http\get(__($c, '/join/{jobType}'), new Handler\RedirectToJobType($c));

//    http\get('/admin', new Handler\Admin\EditLocales($c));

    http\post('/callback/ghost/rebuild', new Handler\PurgeCmsCache($c));

    http\get(__($c, '/error'), new Handler\Error($c));
    http\get(__($c, '/maintenance'), new Handler\Maintenance($c));

    http\post(__($c, '/contact'), new Handler\Contact($c));

    http\get(__($c, '/covid-19'), new Handler\CovidPage($c));
    http\get(__($c, '/covid-19/{page}'), new Handler\CovidPage($c));

    http\get(__($c, '/page/info/coronavirus'), fn(array $params) => Response\redirect('/covid-19'));
    http\get(__($c, '/donate'), fn(array $params) => Response\redirect('/page/donate'));

    http\get(__($c, '/page/{subcategory}/{page}'), fn(array $params) => Response\redirect("/page/{$params['page']}"));
}

function __(Container $c, string $uri): string {
    return IntlUtility::getRoute($c, $uri);
}

function _catch(Exception $e): void {
    // do something
}

if (!Router\get(http\DID_MATCH, false)) {
    Handler\RootHandler::notFound();
}
