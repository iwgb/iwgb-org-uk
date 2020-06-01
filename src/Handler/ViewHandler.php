<?php

namespace Iwgb\OrgUk\Handler;

use Aura\Session\Session as SessionManager;
use Carbon;
use DateTime;
use Guym4c\Airtable\Airtable;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\TwigProps\PropTypesExtension;
use Iwgb\OrgUk\Intl\IntlCache as Cache;
use Iwgb\OrgUk\Intl\IntlCmsAccessTrait;
use Iwgb\OrgUk\Intl\IntlUtility;
use Iwgb\OrgUk\TwigEnvSetupTrait;
use Iwgb\OrgUk\Service\Cms as CmsService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig;
use voku\helper\UTF8;

/**
 * Class RootHandler
 * @package Iwgb\OrgUk\Handler
 */
abstract class ViewHandler extends AbstractHandler {

    use TwigEnvSetupTrait;
    use IntlCmsAccessTrait;

    protected Twig\Environment $view;

    private Ghost $ghost;

    protected Airtable $membership;

    protected Airtable $branches;

    protected IntlUtility $intl;

    protected Cache $cache;

    private SessionManager $sm;

    private Carbon\Factory $datetime;

    protected CmsService $cms;

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

        $this->cms = new CmsService($this->ghost, $this->intl, $this->cache);
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

        $this->populateTemplateEnvironment($request);

        $response->getBody()->write(
            $this->view->render($template, array_merge($data,
                $this->getNavData(),
                $this->getFooterData(),
                ['title' => $title],
            ))
        );

        return $response;
    }

    /**
     * @return array
     */
    private function getNavData(): array {

        return $this->cache->get(Cache::NAV_DATA, function (): array {
            $branches = $this->branches->list('Branches')->getRecords();
            shuffle($branches);

            return [
                'nav' => [
                    'Donate'    => [
                        'kind'   => 'internal',
                        'href'   => '/page/donate',
                        'mdHide' => true,
                    ],
                    'News'      => [
                        'kind' => 'menu',
                        'id'   => 'news',
                        'data' => $this->cms->listPosts(
                            'nav-news',
                            2,
                            $this->cms->withLanguage(),
                        ),
                    ],
                    'Campaigns' => [
                        'kind'   => 'menu',
                        'id'     => 'campaigns',
                        'mdHide' => true,
                        'data' => $this->cms->listPages(
                            'nav-campaigns',
                            null,
                            $this->cms->withLanguage()
                                ->by('tag', '=', $this->intl->getLanguage())
                                ->and('tag', '=', 'category-campaign')
                        ),
                    ],
                    'Branches'  => [
                        'kind' => 'menu',
                        'id'   => 'branches',
                        'data' => $branches,
                    ],
                    'Resources' => [
                        'kind' => 'menu',
                        'id'   => 'resources',
                        'data' => $this->getFallbackPages('category-resources'),
                    ],
                    'About'     => [
                        'kind' => 'menu',
                        'id'   => 'about',
                        'data' => $this->getFallbackPages('category-about'),
                    ],
                ],
            ];
        });
    }

    /**
     * @return array
     */
    private function getFooterData(): array {

        return $this->cache->get(Cache::FOOTER_DATA, fn(): array => ['footer' => [
            'about'     => $this->getFallbackPages('subcategory-about'),
            'resources' => $this->getFallbackPages('subcategory-resources'),
            'legal'     => $this->getFallbackPages('subcategory-legal'),
        ]]);
    }

    private function populateTemplateEnvironment(Request $request): void {

        $this->view->addExtension(new PropTypesExtension($this->view, !$this->settings['dev'], 't'));

        if ($this->settings['dev']) {
            $this->view->addExtension(new Twig\Extension\DebugExtension());
        }

        self::addGlobals($this->view, [
            '_job'       => uniqid(),
            '_language'  => $this->intl->getLanguage(),
            '_languages' => $this->intl->getLanguages(),
            '_fallback'  => $this->intl->getFallback(),
            '_uri'       => IntlUtility::removeFromUri($request->getUri()->getPath()),
            '_url'       => (string) $request->getUri(),
            '_recaptcha' => $this->settings['recaptcha']['siteKey'],
            '_q'         => $request->getQueryParams(),
        ]);

        self::addFunctions($this->view, [

            '_'  => fn(string $page, string $key, array $values = []): string => $this->intl->getText(explode('.', $page)[0], $key, $values),
            '_x' => function (string $s): string {
                if ($this->settings['dev']) {
                    return "/assets{$s}";
                } else {
                    return "{$this->settings['cdn']['assetBaseUrl']}{$s}";
                }
            },
            '_i' => fn(string $imageUri): string => "{$this->settings['cdn']['baseUrl']}{$imageUri}",
            '_a' => fn(string $uri, ?string $lang = null): string => "{$this->intl::addToUri($lang ?? $this->intl->getLanguage(), $uri)}",

            'toIntlKey'     => fn($branch, $key): string => UTF8::str_camelize($branch) . ".{$key}",
            'parseNewLines' => fn(string $s, string $replace = '<br>'): string => UTF8::str_replace("\n", $replace, $s),
            'localeInfo'    => fn(string $language): Carbon\Language => Carbon\Carbon::getAvailableLocalesInfo()[$language],
        ]);

        self::addFilters($this->view, [
            'stripLanguage' => fn(string $uri): string => IntlUtility::removeFromUri($uri),
            'timeAgo'       => fn(DateTime $datetime): string => $this->datetime->instance($datetime)->diffForHumans(),
            'dateFormat'    => fn(DateTime $datetime): string => $this->datetime->instance($datetime)->format('Y-m-d H:i'),
        ]);
    }

    /**
     * @param string $tag
     * @return Cms\Page[]
     * @throws GhostApiException
     */
    private function getFallbackPages(string $tag): array {
        return self::getFallbackPagesByTag($this->ghost, $this->intl, $tag);
    }
}