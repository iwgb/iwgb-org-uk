<?php

namespace Iwgb\OrgUk\Intl;

use Aura\Session\Segment as Session;
use Aura\Session\Session as SessionManager;
//use Negotiation\LanguageNegotiator as Negotiator;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Teapot\StatusCode;

class IntlMiddleware implements MiddlewareInterface {

    private IntlUtility $intl;
    private array $languages;
    private string $fallback;
    private SessionManager $sm;

    public function __construct(IntlUtility $intl, array $languages, SessionManager $sm, ?string $fallback = null) {
        $this->intl = $intl;
        $this->languages = $languages;
        $this->fallback = $fallback ?? $languages[0];
        $this->sm = $sm;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $session = $this->sm->getSegment(self::class);

        $uri = $request->getUri()->getPath();

        $uriLanguage = IntlUtility::getLanguageFromUri($uri);

        $language = $this->validateLanguage(
            $this->negotiateLanguage($request, $session)
        );

        $session->set('language', $language);

        if ($uriLanguage !== null) {

            if ($language !== $uriLanguage) {
                return Psr7::redirect(
                    new Response(),
                    IntlUtility::addToUri($language, $uri),
                    StatusCode::FOUND
                );
            } else {
                return Psr7::redirect(
                    new Response(),
                    IntlUtility::removeFromUri($uri),
                    StatusCode::FOUND
                );
            }
        }

        $this->intl->setLanguage($language);

        return $handler->handle($request->withUri(
            $request->getUri()->withPath(
                IntlUtility::removeFromUri($uri),
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
        $language = IntlUtility::getLanguageFromUri($request->getUri()->getPath());
        if (!empty($language)) {
            return $language;
        }

        // cookie
        $cookieValue = $session->get('language');
        if (!empty($cookieValue)) {
            return $cookieValue;
        }

        // header
//        $acceptHeader = $request->getHeaderLine('Accept');
//        if (!empty($acceptHeader)) {
//            $negotiatedLanguage = (new Negotiator())->getBest($acceptHeader, $this->languages);
//
//            if (!empty($negotiatedLanguage)) {
//                return $negotiatedLanguage;
//            }
//        }

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
}