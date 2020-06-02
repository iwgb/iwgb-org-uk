<?php

namespace Iwgb\OrgUk;

use Carbon;
use DateTime;
use Guym4c\TwigProps\PropTypesExtension;
use Iwgb\OrgUk\Intl\IntlUtility;
use Slim\Psr7\Request;
use Twig;
use Twig\TwigFilter;
use Twig\TwigFunction;
use voku\helper\UTF8;

class RenderEnv {

    private array $settings;

    private Twig\Environment $view;

    private IntlUtility $intl;

    private Carbon\Factory $datetime;

    public function __construct(Twig\Environment $view, array $settings, IntlUtility $intl, Carbon\Factory $datetime) {
        $this->view = $view;
        $this->settings = $settings;
        $this->intl = $intl;
        $this->datetime = $datetime;
    }

    public function init(Request $request): void {

        $this->view->addExtension(new PropTypesExtension($this->view, !$this->settings['is_prod'], 't'));

        if ($this->settings['dev']) {
            $this->view->addExtension(new Twig\Extension\DebugExtension());
        }

        self::addGlobals($this->view, [
            '_job'       => uniqid(),
            '_language'  => $this->intl->getLanguage(),
            '_languages' => $this->intl->getLanguages(),
            '_fallback'  => $this->intl->getFallback(),
            '_path'      => $request->getUri()->getPath(),
            '_uri'       => (string) $request->getUri(),
            '_recaptcha' => $this->settings['recaptcha']['siteKey'],
            '_q'         => $request->getQueryParams(),
        ]);

        self::addFunctions($this->view, [

            '_'  => fn(string $page, string $key, array $values = []): string => $this->intl->getText(explode('.', $page)[0], $key, $values),
            '_i' => fn(string $imageUri): string => "{$this->settings['cdn']['baseUrl']}{$imageUri}",

            'toIntlKey'     => fn($branch, $key): string => UTF8::str_camelize($branch) . ".{$key}",
            'parseNewLines' => fn(string $s, string $replace = '<br>'): string => str_replace("\n", $replace, $s),

        ]);

        self::addFilters($this->view, [
            'stripLanguage' => fn(string $uri): string => IntlUtility::removeFromUri($uri),
            'timeAgo'       => fn(DateTime $datetime): string => $this->datetime->instance($datetime)->diffForHumans(),
            'dateFormat'    => fn(DateTime $datetime): string => $this->datetime->instance($datetime)->format('Y-m-d H:i'),
            'nativeName'    => fn(string $language): string => Carbon\Carbon::getAvailableLocalesInfo()[$language]
                ->getNativeName(),
            'assetUrl'      => function (string $s): string {
                if ($this->settings['is_prod']) {
                    return "{$this->settings['cdn']['assetBaseUrl']}{$s}";
                } else {
                    return "/assets{$s}";
                }
            },
        ]);
    }


    /**
     * @param Twig\Environment $twig
     * @param string[]         $globals
     */
    public static function addGlobals(Twig\Environment $twig, array $globals): void {
        foreach ($globals as $key => $value) {
            $twig->addGlobal($key, $value);
        }
    }

    /**
     * @param Twig\Environment $twig
     * @param callable[]       $functions
     */
    public static function addFunctions(Twig\Environment $twig, array $functions) {
        foreach ($functions as $name => $function) {
            $twig->addFunction(new TwigFunction($name, $function));
        }
    }

    public static function addFilters(Twig\Environment $twig, array $filters) {
        foreach ($filters as $name => $filter) {
            $twig->addFilter(new TwigFilter($name, $filter));
        }
    }
}