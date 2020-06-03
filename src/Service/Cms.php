<?php

namespace Iwgb\OrgUk\Service;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Retrieve;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Iwgb\OrgUk\Intl\IntlCache;
use Iwgb\OrgUk\Intl\IntlCmsResource;
use Iwgb\OrgUk\Intl\IntlUtility;
use voku\helper\UTF8;

class Cms {

    private Ghost $ghost;

    private IntlUtility $intl;

    private IntlCache $cache;

    /**
     * Cms constructor.
     * @param Ghost $ghost
     * @param IntlUtility $intl
     * @param IntlCache $cache
     */
    public function __construct(Ghost $ghost, IntlUtility $intl, IntlCache $cache) {
        $this->ghost = $ghost;
        $this->intl = $intl;
        $this->cache = $cache;
    }

    /**
     * Get a specific page, by slug
     *
     * @param string $slug
     * @return IntlCmsResource
     * @throws GhostApiException
     */
    public function pageBySlug(string $slug): ?IntlCmsResource {
        $fallbackPage = Retrieve\Page::bySlug($this->ghost, $slug);

        if (empty($fallbackPage)) {
            return null;
        }

        return new IntlCmsResource($this->ghost, $this->intl, $fallbackPage);
    }

    /**
     * Get a specific post, by slug
     *
     * @param string $slug
     * @return IntlCmsResource|null
     * @throws GhostApiException
     */
    public function postBySlug(string $slug): ?IntlCmsResource {
        $fallbackPost = Retrieve\Post::bySlug($this->ghost, $slug);

        if (empty($fallbackPost)) {
            return null;
        }

        return new IntlCmsResource($this->ghost, $this->intl, $fallbackPost);
    }

    /**
     * Query posts
     *
     * @param string $id
     * @param int|null $limit
     * @param Filter|null $filter
     * @param int $page
     * @param Sort|null $sort
     * @return IntlCmsResource[]
     */
    public function listPosts(
        string $id,
        ?int $limit = null,
        ?Filter $filter = null,
        int $page = 1,
        ?Sort $sort = null
    ): array {
        return $this->cache->get($id, fn(): array =>
            $this->listResources(Retrieve\Post::class, $id, $limit, $filter, $page, $sort)
        );
    }

    /**
     * Query pages
     *
     * @param string $id
     * @param int|null $limit
     * @param Filter|null $filter
     * @param int $page
     * @param Sort|null $sort
     * @return IntlCmsResource[]
     */
    public function listPages(
        string $id,
        ?int $limit = null,
        ?Filter $filter = null,
        int $page = 1,
        ?Sort $sort = null
    ): array {
        return $this->cache->get($id, fn(): array =>
            $this->listResources(Retrieve\Page::class, $id, $limit, $filter, $page, $sort)
        );
    }

    /**
     * Clear the resource query cache
     */
    public function flushCache(): void {
        $this->ghost->flushCache();
    }

    /**
     * Filter on the fallback language
     *
     * @return Filter
     */
    public function withLanguage(): Filter {
        return (new Filter())
            ->by('tag', '=', $this->intl->getFallback());
    }

    public function pagesByTag(string $tag): array {
        return $this->listPages(
            "nav-{$tag}",
            null,
            $this->withLanguage()
                ->and('tag', '=', $tag)
        );
    }

    /**
     * @param IntlCmsResource[] $resources
     * @return array
     */
    public static function groupBySubcategory(array $resources): array {
        $subcategories = [];
        // go through pages in category
        foreach ($resources as $resource) {
            // iterate over tags
            foreach ($resource->getFallback()->tags as $tag) {
                // if tag is a subcategory
                if (
                    UTF8::str_contains($tag->name, ':')
                    && explode(':', $tag->name)[0] === 'subcategory'
                ) {
                    // fill tag
                    $subcategories[$tag->slug]['tag'] = $tag;

                    // check for intl page
                    $subcategories[$tag->slug]['pages'][] = $resource;
                    continue;
                }
            }
        }
        ksort($subcategories);
        return $subcategories;
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     *
     * @param string $resourceClass
     * @param string $id
     * @param int|null $limit
     * @param Filter|null $filter
     * @param int $page
     * @param Sort|null $sort
     * @return Retrieve\AbstractContentResource[]
     */
    private function listResources(
        string $resourceClass,
        string $id,
        ?int $limit = null,
        ?Filter $filter = null,
        int $page = 1,
        ?Sort $sort = null
    ): array {
        return $this->cache->get($id, fn(): array =>
            IntlCmsResource::getIntlResources($this->ghost, $this->intl,
                $resourceClass::get($this->ghost, $limit,
                    $sort ?? new Sort('published_at', SortOrder::DESC),
                    $filter,
                    $page,
                )->getResources(),
            )
        );
    }



}