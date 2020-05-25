<?php

namespace Iwgb\OrgUk\Intl;

use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use InvalidArgumentException;
use voku\helper\UTF8;

class IntlCmsResource {

    private Ghost $cms;

    private IntlUtility $intl;

    private string $type;

    private ?object $intlResource;

    private object $fallbackResource;

    /**
     * IntlCmsResource constructor.
     * @param Ghost       $cms
     * @param IntlUtility $intl
     * @param       $fallbackResource
     * @throws GhostApiException
     */
    public function __construct(Ghost $cms, IntlUtility $intl, Cms\AbstractContentResource $fallbackResource) {
        $this->cms = $cms;
        $this->intl = $intl;
        $this->fallbackResource = $fallbackResource;

        if ($fallbackResource instanceof Cms\Page) {
            $this->type = 'page';
            $this->intlResource = $this->getIntlPage($fallbackResource);
        } elseif ($fallbackResource instanceof Cms\Post) {
            $this->type = 'post';
            $this->intlResource = $this->getIntlPost($fallbackResource);
        } else {
            throw new InvalidArgumentException("Invalid resource type provided");
        }
    }

    /**
     * @param Cms\Page $page
     * @return Cms\Page|null
     * @throws GhostApiException
     */
    private function getIntlPage(Cms\Page $page): ?Cms\Page {
        if (!$this->intl->isFallback()) {
            $page = self::getIntlResource(fn(string $slug): ?Cms\Page =>
                Cms\Page::bySlug($this->cms, $slug), $this->intl, $page);
            /** @var Cms\Page|null $page */
            return $page;
        }
        return null;
    }

    /**
     * @param Cms\Post $post
     * @return Cms\Post|null
     * @throws GhostApiException
     */
    private function getIntlPost(Cms\Post $post): ?Cms\Post {
        if (!$this->intl->isFallback()) {
            $post = self::getIntlResource(fn(string $slug): ?Cms\Post =>
                Cms\Post::bySlug($this->cms, $slug), $this->intl, $post);
            /** @var Cms\Post|null $post */
            return $post;
        }
        return null;
    }

    /**
     * @param callable                    $getResource
     * @param IntlUtility                 $intl
     * @param Cms\AbstractContentResource $resource
     * @return Cms\AbstractContentResource|null
     * @throws GhostApiException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    private static function getIntlResource(callable $getResource, IntlUtility $intl, Cms\AbstractContentResource $resource) {
        try {
            $intlPage = $getResource("{$resource->slug}-{$intl->getLanguage()}");
        } catch (GhostApiException $e) {
            if (UTF8::str_contains($e->getMessage(), 'NotFoundError')) {
                $intlPage = null;
            } else {
                throw $e;
            }
        }
        return $intlPage;
    }

    /**
     * @param Ghost       $cms
     * @param IntlUtility $intl
     * @param array       $resources
     * @return array
     * @throws GhostApiException
     */
    public static function getIntlResources(Ghost $cms, IntlUtility $intl, array $resources): array {
        $intlResources = [];
        foreach ($resources as $resource) {
            $intlResources[] = new self($cms, $intl, $resource);
        }
        return $intlResources;
    }

    /**
     * @return Cms\Post|Cms\Page|null
     */
    public function getIntl() {
        return $this->intlResource;
    }

    /**
     * @return Cms\Post|Cms\Page
     */
    public function getFallback() {
        /** @var Cms\Post|Cms\Page $resource */
        $resource = $this->fallbackResource;
        return $resource;
    }
}