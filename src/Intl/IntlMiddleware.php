<?php

namespace Iwgb\OrgUk\Intl;

use Aura\Session\Segment as Session;
use Aura\Session\Session as SessionManager;
use Guym4c\PhpS3Intl\IntlController;
use Negotiation\BaseAccept;
use Negotiation\LanguageNegotiator as Negotiator;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Teapot\StatusCode;
use voku\helper\UTF8;

class IntlMiddleware implements MiddlewareInterface {

    private const LANGUAGE_IN_URI_REGEX = "/^\/(?<language>[a-z]{2})\//";

    private IntlController $intl;
    private array $languages;
    private string $fallback;
    private SessionManager $sm;

    public function __construct(IntlController $intl, array $languages, SessionManager $sm, ?string $fallback = null) {
        $this->intl = $intl;
        $this->languages = $languages;
        $this->fallback = $fallback ?? $languages[0];
        $this->sm = $sm;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $session = $this->sm->getSegment(self::class);

        $uri = $request->getUri()->getPath();

        $uriLanguage = self::getLanguageFromUri($uri);

        $language = $this->validateLanguage(
            $this->negotiateLanguage($request, $session)
        );

        $session->set('language', $language);

        if ($uriLanguage !== null) {

            if ($language !== $uriLanguage) {
                return Psr7::redirect(
                    new Response(),
                    self::addToUri($language, $uri),
                    StatusCode::FOUND
                );
            } else {
                return Psr7::redirect(
                    new Response(),
                    self::removeFromUri($uri),
                    StatusCode::FOUND
                );
            }
        }

        $this->intl->setLanguage($language);

        return $handler->handle($request->withUri(
            $request->getUri()->withPath(
                self::removeFromUri($uri),
            ),
        ));
    }

    /**
     * Negotiate a language to be used, checking:
     * 1. Any language in the URI
     * 2. else, any language previously set by this middleware in the session, else
     * 3. else, a language is attempted to be negotiated through the client's Accept header, else
     * 4. else, the fallback language is used
     *
     * @param RequestInterface $request
     * @param Session $session
     * @return string
     */
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

        // header
        $acceptHeader = $request->getHeaderLine('Accept');
        if (!empty($acceptHeader)) {
            /** @var BaseAccept $negotiatedLanguage */
            $negotiatedLanguage = (new Negotiator())->getBest($acceptHeader, $this->languages);

            if (!empty($negotiatedLanguage)) {
                return $negotiatedLanguage->getType();
            }
        }

        return $this->fallback;
    }

    /**
     * Check whether the language is a supported language.
     * If not, revert to the fallback language.
     *
     * @param string $language
     * @return string
     */
    private function validateLanguage(string $language): string {
        return in_array($language, $this->languages)
            ? $language
            : $this->fallback;
    }

    public function isFallback(): bool {
        return $this->language === $this->fallback;
    }

    /**
     * Add the language to the URI
     *
     * @param string $language
     * @param string $uri
     * @return string
     */
    public static function addToUri(string $language, string $uri): string {
        $uri = self::formatUri($uri);

        if (self::getLanguageFromUri($uri) === $language) {
            return $uri;
        }

        $uri = self::removeFromUri($uri);
        return "/{$language}{$uri}";
    }

    /**
     * Remove the language prefix from $uri
     *
     * @param string $uri
     * @return string|null
     */
    public static function removeFromUri(string $uri): ?string {
        $uri = self::formatUri($uri);
        $language = self::getLanguageFromUri($uri);

        if (empty($language)) {
            return $uri;
        } else {
            if (UTF8::strlen($uri) === UTF8::strlen($language) + 1) {
                return '/';
            } else {
                return str_replace("/{$language}/", '/', $uri);
            }
        }
    }

    /**
     * Retrieve the language from the URI
     * Returns null if no language is in the URI
     *
     * @param string $uri
     * @return string|null
     */
    public static function getLanguageFromUri(string $uri): ?string {
        $uri = self::formatUri($uri);

        if (UTF8::strlen($uri) !== 3) {
            $matches = [];
            preg_match(self::LANGUAGE_IN_URI_REGEX, $uri, $matches);
            return $matches['language'] ?? null;
        } else {
            return UTF8::substr($uri, 1);
        }
    }

    /**
     * Ensure $uri is correctly formed, with a starting slash
     *
     * @param string $uri
     * @return string
     */
    private static function formatUri(string $uri): string {
        return UTF8::char_at($uri, 0) !== '/'
            ? "/{$uri}"
            : $uri;
    }
}