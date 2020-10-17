<?php

use DI\Container;
use Iwgb\OrgUk\Handler\ErrorHandler;
use Iwgb\OrgUk\Intl\IntlMiddleware;
use Iwgb\OrgUk\Provider\Provider;
use Middlewares\TrailingSlash as TrailingSlashMiddleware;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;

/** @var Container $c */
$c = require '../bootstrap.php';

$app = AppFactory::createFromContainer($c);

$app->add(new ContentLengthMiddleware());

$callableResolver = $app->getCallableResolver();

(require APP_ROOT . '/app/routes.php')($app, $c);

$app->addRoutingMiddleware();

$app->add(new IntlMiddleware(
    $c->get(Provider::INTL),
    $c->get(Provider::SETTINGS)['languages'],
    $c->get(Provider::SESSION)
));

$app->add(new TrailingSlashMiddleware(false));

$app->addErrorMiddleware(
    !$c->get(Provider::SETTINGS)['is_prod'],
    true,
    true,
)->setDefaultErrorHandler(new ErrorHandler(
    $app->getCallableResolver(),
    $app->getResponseFactory(),
));

$app->run();
