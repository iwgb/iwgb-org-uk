<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Iwgb\OrgUk\Intl;
use voku\helper\UTF8;

trait IntlCmsAccessTrait {

    /**
     * @param Ghost  $cms
     * @param Intl   $intl
     * @param string $tag
     * @return Cms\Post[]
     * @throws GhostApiException
     */
    private static function getFallbackPagesByTag(Ghost $cms, Intl $intl, string $tag): array {
        return self::populateCategoryMenu($cms, $intl, Cms\Page::get($cms, null,
            new Sort('title', SortOrder::DESC),
            (new Filter())->by('tag', '=', $intl->getFallback())
                ->and('tag', '=', $tag))
            ->getResources());
    }

    /**
     * @param Ghost      $cms
     * @param Intl       $intl
     * @param Cms\Page[] $pages
     * @return Cms\Page[]
     * @throws GhostApiException
     */
    private static function populateCategoryMenu(Ghost $cms, Intl $intl, array $pages): array {
        $subcategories = [];
        // go through pages in category
        foreach ($pages as $page) {
            // iterate over tags
            foreach ($page->tags as $tag) {
                // if tag is a subcategory
                if (UTF8::str_contains($tag->name, ':')
                    && explode(':', $tag->name)[0] === 'subcategory') {

                    // fill tag
                    $subcategories[$tag->slug]['tag'] = $tag;

                    // check for intl page
                    $subcategories[$tag->slug]['pages'][] = self::populatePageGroup($cms, $intl, $page);

                    continue;
                }
            }
        }
        ksort($subcategories);
        return $subcategories;
    }

    /**
     * @param Ghost $cms
     * @param Intl  $intl
     * @param array $pages
     * @return array
     * @throws GhostApiException
     */
    protected static function populatePageGroups(Ghost $cms, Intl $intl, array $pages): array {
        $groups = [];
        foreach ($pages as $page) {
            $groups[] = self::populatePageGroup($cms, $intl, $page);
        }
        return $groups;
    }

    /**
     * @param Ghost    $cms
     * @param Intl     $intl
     * @param Cms\Page $page
     * @return array
     * @throws GhostApiException
     */
    protected static function populatePageGroup(Ghost $cms, Intl $intl, Cms\Page $page): array {
        $pageGroup = [$intl->getFallback() => $page];

        if (!$intl->isFallback()) {
            $intlPage = self::getIntlPage($cms, $intl, $page);
            if (!empty($intlPage)) {
                $pageGroup[$intl->getLanguage()] = $intlPage;
            }
        }

        return $pageGroup;
    }

    /**
     * @param Ghost $cms
     * @param Intl  $intl
     * @param array $posts
     * @return array
     * @throws GhostApiException
     */
    protected static function populatePostGroups(Ghost $cms, Intl $intl, array $posts): array {
        $groups = [];
        foreach ($posts as $post) {
            $groups[] = self::populatePostGroup($cms, $intl, $post);
        }
        return $groups;
    }

    /**
     * @param Ghost    $cms
     * @param Intl     $intl
     * @param Cms\Post $post
     * @return array
     * @throws GhostApiException
     */
    protected static function populatePostGroup(Ghost $cms, Intl $intl, Cms\Post $post): array {
        $postGroup = [$intl->getFallback() => $post];

        if (!$intl->isFallback()) {
            $intlPost = self::getIntlPost($cms, $intl, $post);
            if (!empty($intlPost)) {
                $postGroup[$intl->getLanguage()] = $intlPost;
            }
        }

        return $postGroup;
    }

    /**
     * @param Ghost    $cms
     * @param Intl     $intl
     * @param Cms\Page $page
     * @return Cms\Page|null
     * @throws GhostApiException
     */
    protected static function getIntlPage(Ghost $cms, Intl $intl, Cms\Page $page): ?Cms\Page {
        return self::getIntlResource(fn(string $slug): ?Cms\Page => Cms\Page::bySlug($cms, $slug), $intl, $page);
    }

    /**
     * @param Ghost    $cms
     * @param Intl     $intl
     * @param Cms\Post $post
     * @return Cms\Post|null
     * @throws GhostApiException
     */
    protected static function getIntlPost(Ghost $cms, Intl $intl, Cms\Post $post): ?Cms\Post {
        return self::getIntlResource(fn(string $slug): ?Cms\Post => Cms\Post::bySlug($cms, $slug), $intl, $post);
    }

    /**
     * @param callable                    $getResource
     * @param Intl                        $intl
     * @param Cms\AbstractContentResource $resource
     * @return Cms\Page|Cms\Post
     * @throws GhostApiException
     */
    private static function getIntlResource(callable $getResource, Intl $intl, Cms\AbstractContentResource $resource) {
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

}