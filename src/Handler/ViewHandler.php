<?php

namespace Iwgb\OrgUk\Handler;

use Aura\Session\Session as SessionManager;
use Carbon;
use Guym4c\Airtable\Airtable;
use Guym4c\GhostApiPhp\Ghost;
use Iwgb\OrgUk\Intl\IntlCache;
use Iwgb\OrgUk\Intl\IntlUtility;
use Iwgb\OrgUk\RenderEnv;
use Iwgb\OrgUk\Service\LayoutData;
use Iwgb\OrgUk\Service\Cms;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig;

/**
 * Class RootHandler
 * @package Iwgb\OrgUk\Handler
 */
abstract class ViewHandler extends AbstractHandler {

    protected Twig\Environment $view;

    private Ghost $ghost;

    protected Airtable $membership;

    protected Airtable $branches;

    protected IntlUtility $intl;

    protected IntlCache $cache;

    private SessionManager $sm;

    private Carbon\Factory $datetime;

    protected Cms $cms;

    protected LayoutData $layoutData;

    public function __construct(ContainerInterface $c) {
        parent::__construct($c);

        $this->view = $c->get('view');
        $this->settings = $c->get('settings');
        $this->ghost = $c->get('cms');
        $this->membership = $c->get('membership');
        $this->branches = $c->get('branches');
        $this->cache = $c->get('cache');
        $this->sm = $c->get('session');
        $this->datetime = $c->get('datetime');
        $this->intl = $c->get('intl');

        $this->cms = new Cms($this->ghost, $this->intl, $this->cache);
        $this->layoutData = new LayoutData($this->cms, $this->branches, $this->cache, $this->intl);
    }

    /**
     * {@inheritDoc}
     * @throws HttpNotFoundException
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    abstract public function __invoke(Request $request, Response $response, array $args): ResponseInterface;

    /**
     * @param Request $request
     * @param Response $response
     * @param string $template
     * @param string $title
     * @param array $data
     * @return ResponseInterface
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    protected function render(
        Request $request,
        Response $response,
        string $template,
        string $title,
        array $data = []
    ): ResponseInterface {

        (new RenderEnv($this->view, $this->settings, $this->intl, $this->datetime))
            ->init($request);

        $response->getBody()->write(
            $this->view->render($template, array_merge($data,
                $this->layoutData->nav(),
                $this->layoutData->footer(),
                ['title' => $title],
            )),
        );

        return $response;
    }


}