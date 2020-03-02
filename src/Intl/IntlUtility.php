<?php

namespace Iwgb\OrgUk\Intl;

use Aura\Session\Segment as Session;
use Aura\Session\Session as SessionManager;
use Guym4c\GhostApiPhp\Filter;
use Negotiation\LanguageNegotiator as Negotiator;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Siler\Http\Request;
use stdClass;
use voku\helper\UTF8;

class IntlUtility {

    private const LANGUAGE_IN_URI_REGEX = "/^\/(?<language>[a-z]{2})\//";

    private string $language;
    private array $languages;
    private string $fallback;

    public function __construct(array $languages, RequestInterface $request, SessionManager $sm, callable $redirect, ?string $fallback = null) {
        $this->languages = $languages;
        $this->fallback = $fallback ?? $languages[0];
        $this->language = $this->processLanguage($request, $sm, $redirect);

        if (Request\get('persistLocale') == 'yes') {
            session_set_cookie_params(['lifetime' => 60 * 60 * 24 * 365 /* 1 year */]);
        }
    }

    private function processLanguage(RequestInterface $request, SessionManager $sm, callable $redirect): string {
        $session = $sm->getSegment(self::class);
        $uri = $request->getUri()->getPath();
        $uriLanguage = self::getLanguageFromUri($request->getUri()->getPath());
        $language = $this->validateLanguage($this->negotiateLanguage($request, $session));

        if ($language != $uriLanguage && $language != $this->fallback) {
            $redirect(self::addToUri($language, $uri));
        }

        $session->set('language', $language);
        return $language;
    }

    private function negotiateLanguage(RequestInterface $request, Session $session): string {

        // uri
        $language = self::getLanguageFromUri($request->getUri()->getPath());
        if (!empty($language)) {
            return $language;
        }

        // cookie
        $cookieValue = $session->get('language');
        if (!empty($cookieValue)) {
            return $cookieValue;
        }

        if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }

        // header
        $acceptHeader = $request->getHeaderLine('Accept');
        if (!empty($acceptHeader)) {
            $negotiatedLanguage = (new Negotiator())->getBest($acceptHeader, $this->languages);

            if (!empty($negotiatedLanguage)) {
                return $negotiatedLanguage;
            }
        }

        return $this->fallback;
    }

    public function getText(string $template, string $key): string {
        if (strpos($template, "/")) {
            $page = explode('/', $template)[0];
        } else {
            $page = explode('.', $template)[0];
        }
        return $this->get(UTF8::strtolower($page), $key);
    }

    /**
     * @param string      $page
     * @param string      $key
     * @param string|null $language
     * @return string|string[]
     */
    private function get(string $page, string $key, string $language = null) {

        $language = $language ?? $this->language;

        $json = self::read($page, $language);

        // page not found
        if ($json === null) {
            self::write($page, $language, new stdClass());

            if ($language != $this->fallback) {
                return $this->get($page, $key, $this->fallback);
            }
            return '';
        }

        // key does not exist
        if (!isset($json[$key])) {
            $json[$key] = '';
            self::write($page, $language, $json);

            if ($language != $this->fallback) {
                return $this->get($page, $key, $this->fallback);
            }

            return '';
        }

        $value = $json[$key];

        if ($value === ''
            && $language != $this->fallback) {
            return $this->get($page, $key, $this->fallback);
        }

        return preg_replace_callback('/{ *([a-zA-Z0-9\-_.]+) *}/',
            fn(array $matches): string => $this->get($page, $matches[1]),
            $value
        );
    }

    public static function readAll(string $language): array {
        $components = [];
        foreach (scandir(APP_ROOT . '/intl') as $component) {
            if (!in_array($component, ['.', '..'])) {
                $components[$component] = self::read($component, $language);
            }
        }
        return $components;
    }

    public static function read(string $page, string $language): ?array {
        $file = self::file($page, $language);
        if (file_exists($file)) {
            return UTF8::json_decode(
                UTF8::file_get_contents(self::file($page, $language)), true);
        }

        return null;
    }

    public static function write(string $page, string $language, $data): void {
        ksort($data, SORT_STRING);
        file_put_contents(self::file($page, $language), UTF8::json_encode($data));
    }

    private static function file(string $page, string $language): string {
        return APP_ROOT . "/intl/{$page}/{$language}.json";
    }

    public static function addToUri(string $language, string $uri): string {
        $uri = self::formatUri($uri);

        if (self::getLanguageFromUri($uri) === $language) {
            return $uri;
        }

        $uri = self::removeFromUri($uri);
        return "/{$language}{$uri}";
    }

    public static function removeFromUri(string $uri): ?string {
        $uri = self::formatUri($uri);
        $language = self::getLanguageFromUri($uri);

        if (empty($language)) {
            return $uri;
        } else {
            return UTF8::str_replace("/{$language}/", '/', $uri);
        }
    }

    public static function getLanguageFromUri(string $uri): ?string {
        $uri = self::formatUri($uri);
        $matches = [];
        preg_match(self::LANGUAGE_IN_URI_REGEX, $uri, $matches);
        return $matches[1] ?? null;
    }

    public static function uriHasLanguage(string $uri): bool {
        return self::getLanguageFromUri($uri) !== null;
    }

    private static function formatUri(string $uri): string {
        if (UTF8::char_at($uri, 0) !== '/') {
            $uri = "/{$uri}";
        }
        return $uri;
    }

    private function validateLanguage(string $language): string {
        return in_array($language, $this->languages)
            ? $language
            : $this->fallback;
    }

    public function generateRoute(string $uri): string {
        return "/({$this->language}/)?{$uri}";
    }

    public static function getRoute(Container $c, string $uri): string {
        return '(/' . $c['intl']->getLanguage() . ")?${uri}";
    }

    public function ghostFilterFactory(): Filter {
        return (new Filter())->by('tag', '=', $this->getFallback());
    }

    public function isFallback(): bool {
        return $this->language == $this->fallback;
    }

    /**
     * @return string
     */
    public function getLanguage(): string {
        return $this->language;
    }

    /**
     * @return array
     */
    public function getLanguages(): array {
        return $this->languages;
    }

    /**
     * @return string
     */
    public function getFallback(): string {
        return $this->fallback;
    }
}