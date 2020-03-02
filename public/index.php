<?php

$c = require '../bootstrap.php';

use Iwgb\OrgUk\Handler;
use Pimple\Container;
use Siler\Route as http;
use Iwgb\OrgUk\Intl\IntlUtility;

http\get("/assets/{type}/(?'file'[A-z0-9\/_-]+)\.{ext}", new Handler\AssetProxy($c));

http\get(_($c, ''), new Handler\Home($c));

http\get(_($c, "/post/(?'id'5[a-f][0-9a-f]{11})/{slug}"), new Handler\LegacyPost($c));
http\get(_($c, '/post/{slug}'), new Handler\Post($c));
http\get(_($c, '/page/{slug}'), new Handler\Page($c));

http\get(_($c, '/join'), new Handler\Join($c));

http\get('/admin', new Handler\Admin\EditLocales($c));

http\post('/callback/ghost/rebuild', new Handler\PurgeCmsCache($c));

function _(Container $c, string $uri): string {
    return IntlUtility::getRoute($c, $uri);
}
