<?php

namespace Iwgb\OrgUk\Service;

use Guym4c\Airtable\Airtable;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\PhpS3Intl\IntlController;
use Iwgb\OrgUk\Intl\CmsResource;
use Iwgb\OrgUk\Intl\IntlCache;

class LayoutData {

    private Cms $cms;

    private Airtable $branches;

    private IntlCache $cache;

    private IntlController $intl;

    /**
     * LayoutData constructor.
     * @param Cms $cms
     * @param Airtable $branches
     * @param IntlCache $cache
     * @param IntlController $intl
     */
    public function __construct(Cms $cms, Airtable $branches, IntlCache $cache, IntlController $intl) {
        $this->cms = $cms;
        $this->branches = $branches;
        $this->cache = $cache;
        $this->intl = $intl;
    }

    /**
     * @return array
     * @throws AirtableApiException
     * @throws GhostApiException
     */
    public function nav(): array {
        return $this->cache->get('nav',
            function (): array {
                $branches = $this->branches->list('Branches')->getRecords();
                shuffle($branches);

                foreach ($branches as $i => $branch) {
                    if ($branch->Name === 'Central Union') {
                        $centralUnion = $branch;
                        unset($branches[$i]);
                        $branches[] = $centralUnion;
                        break;
                    }
                }

                return ['nav' => [
                    'News'      => [
                        'kind' => 'menu',
                        'id'   => 'news',
                        'data' => $this->cms->listPosts(
                            'nav-news',
                            2,
                            $this->cms->withLanguage()
                                ->and('tag', '=', 'press-release'),
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
                    'About'     => [
                        'kind' => 'menu',
                        'id'   => 'about',
                        'data' => Cms::groupBySubcategory(
                            $this->cms->pagesByTag('category-about')
                        ),
                    ],
                    'Support' => [
                        'kind' => 'internal',
                        'id'   => 'support',
                        'href' => '/page/support-and-advice'
                    ],
                ]];
            },
            function (array $data): array {
                $data['nav']['News']['data'] =
                    CmsResource::getCacheableFromAll($data['nav']['News']['data']);
                $data['nav']['Campaigns']['data'] =
                    CmsResource::getCacheableFromAll($data['nav']['Campaigns']['data']);
                $data['nav']['About']['data'] =
                    self::cacheableResourcesFromSubcategoryList($data['nav']['About']['data']);

                return $data;
            },
            function (array $data): array {
                $data['nav']['News']['data'] =
                    CmsResource::buildAllFromCachedObjects($this->cms->ghost, $this->intl, $data['nav']['News']['data']);
                $data['nav']['Campaigns']['data'] =
                    CmsResource::buildAllFromCachedObjects($this->cms->ghost, $this->intl, $data['nav']['Campaigns']['data']);
                $data['nav']['About']['data'] =
                    self::buildResourcesFromCachedSubcategories($this->cms->ghost, $this->intl, $data['nav']['About']['data']);

                return $data;
            },
        );
    }

    /**
     * @return array
     * @throws GhostApiException
     */
    public function footer(): array {
        return $this->cache->get('footer',
            fn(): array => ['footer' => [
                'policies'     => $this->cms->pagesByTag('category-policies'),
                'about' => $this->cms->pagesByTag('subcategory-about'),
                'getInvolved'     => $this->cms->pagesByTag('subcategory-get-involved'),
            ]],
            function (array $categories): array {
                $cacheable = [];
                foreach ($categories['footer'] as $category => $resources) {
                    $cacheable[$category] = CmsResource::getCacheableFromAll($resources);
                }
                return ['footer' => $cacheable];
            },
            function (array $cached): array {
                $categories = [];
                foreach ($cached['footer'] as $category => $resources) {
                    $categories[$category] = CmsResource::buildAllFromCachedObjects($this->cms->ghost, $this->intl, $resources);
                }
                return ['footer' => $categories];
            },
        );
    }

    private static function cacheableResourcesFromSubcategoryList(array $subcategories): array {
        $cacheable = [];
        foreach ($subcategories as $subcategory => $resources) {
            $cacheable[$subcategory]['pages'] = CmsResource::getCacheableFromAll($resources['pages']);
            $cacheable[$subcategory]['tag'] = $resources['tag'];
        }

        return $cacheable;
    }

    private static function buildResourcesFromCachedSubcategories(
        Ghost $ghost,
        IntlController $intl,
        array $cached
    ): array {
        $subcategories = [];
        foreach ($cached as $subcategory => $resources) {
            $subcategories[$subcategory]['pages'] = CmsResource::buildAllFromCachedObjects($ghost, $intl, $resources['pages']);
            $subcategories[$subcategory]['tag'] = $resources['tag'];
        }
        return $subcategories;
    }
}