<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

abstract class AbstractHandler {

    protected array $settings;

    public function __construct(ContainerInterface $c) {
        $this->settings = $c->get('settings');
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