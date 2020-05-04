<?php

namespace Iwgb\OrgUk\Handler;

use Aura\Session\Session as SessionManager;
use Carbon;
use DateTime;
use Guym4c\Airtable\Airtable;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Guym4c\TwigProps\PropTypesExtension;
use Iwgb\OrgUk\Intl\IntlCache as Cache;
use Iwgb\OrgUk\Intl\IntlCmsAccessTrait;
use Iwgb\OrgUk\Intl\IntlCmsResource;
use Iwgb\OrgUk\Intl\IntlUtility;
use Iwgb\OrgUk\TwigEnvSetupTrait;
use Pimple\Container;
use Psr\Http\Message\ServerRequestInterface;
use Siler\Http\Request;
use Siler\Http\Response;
use Siler\Twig as Template;
use Twig;
use voku\helper\UTF8;

/**
 * Class RootHandler
 * @package Iwgb\OrgUk\Handler
 */
abstract class RootHandler {

    use TwigEnvSetupTrait;

    use IntlCmsAccessTrait;

    protected Twig\Environment $view;

    protected array $settings;

    protected ServerRequestInterface $request;

    protected Ghost $cms;

    protected Airtable $membership;

    protected Airtable $branches;

    protected IntlUtility $intl;

    private array $time;

    protected Cache $cache;

    private SessionManager $sm;

    private Carbon\Factory $datetime;

    public function __construct(Container $c) {
        $this->view = $c['view'];
        $this->settings = $c['settings'];
        $this->request = $c['request'];
        $this->intl = $c['intl'];
        $this->cms = $c['cms'];
        $this->membership = $c['membership'];
        $this->branches = $c['branches'];
        $this->cache = $c['cache'];
        $this->sm = $c['session'];
        $this->datetime = $c['datetime'];
    }

    /**
     * @param array $routeParams
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     * @throws AirtableApiException
     * @throws GhostApiException
     */
    abstract public function __invoke(array $routeParams): void;

    /**
     * @param string $template
     * @param string $title
     * @param array  $data
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    protected function render(string $template, string $title, array $data = []) {

        $this->populateTemplateEnvironment();

        Response\html(Template\render($template, array_merge($data,
            $this->getNavData(),
            $this->getFooterData(),
            ['title' => $title]
        )));
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
                    //TODO i18n
                    'News'      => [
                        'kind' => 'menu',
                        'id'   => 'news',
                        'data' => IntlCmsResource::getIntlResources($this->cms, $this->intl,
                            Cms\Post::get($this->cms, 2,
                                new Sort('published_at', SortOrder::DESC),
                                $this->intl->ghostFilterFactory()
                            )->getResources()
                        ),
                    ],
                    'Campaigns' => [
                        'kind'   => 'menu',
                        'id'     => 'campaigns',
                        'data'   => Cms\Page::get($this->cms, null,
                            new Sort('published_at', SortOrder::DESC),
                            $this->intl->ghostFilterFactory()
                                ->and('tag', '=', 'category-campaign')
                        )->getResources(),
                        'mdHide' => true,
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

    private function populateTemplateEnvironment(): void {

        $this->view->addExtension(new PropTypesExtension($this->view, !$this->settings['dev'], 't'));

        if ($this->settings['dev']) {
            $this->view->addExtension(new Twig\Extension\DebugExtension());
        }

        self::addGlobals($this->view, [
            '_job'       => uniqid(),
            '_language'  => $this->intl->getLanguage(),
            '_languages' => $this->intl->getLanguages(),
            '_fallback'  => $this->intl->getFallback(),
            '_uri'       => IntlUtility::removeFromUri($this->request->getUri()->getPath()),
            '_url'       => (string)$this->request->getUri(),
            '_recaptcha' => $this->settings['recaptcha']['siteKey'],
            '_q'         => Request\get(),
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
        return self::getFallbackPagesByTag($this->cms, $this->intl, $tag);
    }

    public static function notFound(): void {
        Response\redirect('/?notFound');
    }
}