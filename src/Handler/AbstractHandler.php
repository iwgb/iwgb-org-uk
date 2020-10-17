<?php

namespace Iwgb\OrgUk\Handler;

use Iwgb\OrgUk\Provider\Provider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

abstract class AbstractHandler {

    protected array $settings;

    public function __construct(ContainerInterface $c) {
        $this->settings = $c->get(Provider::SETTINGS);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @return ResponseInterface
     */
    abstract public function __invoke(Request $request, Response $response, array $args): ResponseInterface;
}