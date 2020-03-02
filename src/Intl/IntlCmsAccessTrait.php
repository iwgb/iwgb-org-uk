<?php

namespace Iwgb\OrgUk\Intl;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use voku\helper\UTF8;

trait IntlCmsAccessTrait {

    /**
     * @param Ghost       $cms
     * @param IntlUtility $intl
     * @param string      $tag
     * @return Cms\Post[]
     * @throws GhostApiException
     */
    private static function getFallbackPagesByTag(Ghost $cms, IntlUtility $intl, string $tag): array {
        return self::populateCategoryMenu($cms, $intl, Cms\Page::get($cms, null,
            new Sort('title', SortOrder::DESC),
            (new Filter())->by('tag', '=', $intl->getFallback())
                ->and('tag', '=', $tag))
            ->getResources());
    }

    /**
     * @param Ghost       $cms
     * @param IntlUtility $intl
     * @param Cms\Page[]  $pages
     * @return Cms\Page[]
     * @throws GhostApiException
     */
    private static function populateCategoryMenu(Ghost $cms, IntlUtility $intl, array $pages): array {
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
                    $subcategories[$tag->slug]['pages'][] = new IntlCmsResource($cms, $intl, $page);
                    continue;
                }
            }
        }
        ksort($subcategories);
        return $subcategories;
    }
}