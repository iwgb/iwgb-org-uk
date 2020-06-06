<?php

use Iwgb\OrgUk\Handler\ErrorHandler;
use Iwgb\OrgUk\Intl\IntlMiddleware;
use Middlewares\TrailingSlash;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;

$c = require '../bootstrap.php';

$app = AppFactory::createFromContainer($c);

$app->add(new ContentLengthMiddleware());


$callableResolver = $app->getCallableResolver();

(require APP_ROOT . '/app/routes.php')($app);

$app->addRoutingMiddleware();

$app->add(new IntlMiddleware(
        $c->get('intl'),
        $c->get('settings')['languages'],
        $c->get('session'))
);
$app->add(new TrailingSlash(false));

$app->addErrorMiddleware(
    !$c->get('settings')['is_prod'],
    true,
    true,
)->setDefaultErrorHandler(new ErrorHandler(
    $app->getCallableResolver(),
    $app->getResponseFactory(),
));

$app->run();
