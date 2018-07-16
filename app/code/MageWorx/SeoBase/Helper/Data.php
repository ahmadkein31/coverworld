<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * SEO Base config data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_HREFLANGS_ENABLED                       = 'mageworx_seo/base/hreflangs/enabled';
    const XML_PATH_HREFLANGS_CATEGORY_ENABLED              = 'mageworx_seo/base/hreflangs/enabled_category';
    const XML_PATH_HREFLANGS_PRODUCT_ENABLED               = 'mageworx_seo/base/hreflangs/enabled_product';
    const XML_PATH_HREFLANGS_CMS_ENABLED                   = 'mageworx_seo/base/hreflangs/enabled_cms';
    const XML_PATH_HREFLANGS_SCOPE                         = 'mageworx_seo/base/hreflangs/scope';
    const XML_PATH_HREFLANGS_USE_MAGENTO_LANGUAGE_CODE     = 'mageworx_seo/base/hreflangs/use_magento_lang_code';
    const XML_PATH_HREFLANGS_LANGUAGE_CODE                 = 'mageworx_seo/base/hreflangs/lang_code';
    const XML_PATH_HREFLANGS_COUNTRY_CODE_ENABLED          = 'mageworx_seo/base/hreflangs/country_code_enabled';
    const XML_PATH_HREFLANGS_USE_MAGENTO_COUNTRY_CODE      = 'mageworx_seo/base/hreflangs/use_magento_country_code';
    const XML_PATH_HREFLANGS_COUNTRY_CODE                  = 'mageworx_seo/base/hreflangs/country_code';
    const XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE   = 'mageworx_seo/base/hreflangs/x_default_global';
    const XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE  = 'mageworx_seo/base/hreflangs/x_default_website';
    const XML_PATH_HREFLANGS_CMS_RELATION_WAY              = 'mageworx_seo/base/hreflangs/cms_relation_way';
    const XML_PATH_MAGENTO_LANGUAGE_CODE                   = 'general/locale/code';
    const XML_PATH_MAGENTO_COUNTRY_CODE                    = 'general/country/default';
    const XML_PATH_HOME_PAGE                               = 'web/default/cms_home_page';


    /**
     * XML config path canonical enabled
     */
    const XML_PATH_CANONICAL_URL_ENABLED      = 'mageworx_seo/base/canonical/use_canonical';

    /**
     * XML config path canonical ignore pages
     */
    const XML_PATH_CANONICAL_IGNORE_PAGES     = 'mageworx_seo/base/canonical/canonical_ignore_pages';

    /**
     * XML config path trailing slash for home page URL
     */
    const XML_PATH_TRAILING_SLASH_FOR_HOME     = 'mageworx_seo/base/canonical/trailing_slash_home_page';

    /**
     * XML config path trailing slash for URL
     */
    const XML_PATH_TRAILING_SLASH              = 'mageworx_seo/base/canonical/trailing_slash';

    /**
     * XML config path use pager param in canonical URL
     */
    const XML_PATH_USE_PAGER_FOR_CANONICAL     = 'mageworx_seo/base/canonical/use_pager_in_canonical';

    /**
     * XML config path use canonical URL for layered navigation pages
     */
    const XML_PATH_CANONICAL_FOR_LN            = 'mageworx_seo/base/canonical/canonical_for_ln';

    /**
     * XML config path cross domain store for canonical URL
     */
    const XML_PATH_CROSS_DOMAIN_STORE          = 'mageworx_seo/base/canonical/cross_domain_store';

    /**
     * XML config path canonical cross domain URL
     */
    const XML_PATH_CROSS_DOMAIN_URL            = 'mageworx_seo/base/canonical/cross_domain_url';

    /**
     * XML config path use canonical for associated products
     */
    const XML_PATH_ASSOCIATED_TYPES            = 'mageworx_seo/base/canonical/associated_types';

    /**
     * XML config path https robots
     */
    const XML_PATH_HTTPS_ROBOTS                = 'mageworx_seo/base/robots/https_robots';

    /**
     * XML config path pages for noindex, follow robots
     */
    const XML_PATH_NOINDEX_PAGES               = 'mageworx_seo/base/robots/noindex_follow_pages';

    /**
     * XML config path user pages for noindex, follow robots
     */
    const XML_PATH_NOINDEX_USER_PAGES          = 'mageworx_seo/base/robots/noindex_follow_user_pages';

    /**
     * XML config path user pages for noindex, nofollow robots
     */
    const XML_PATH_NOINDEX_NOFOLLOW_USER_PAGES = 'mageworx_seo/base/robots/noindex_nofollow_user_pages';

    /**
     * XML config path layered navigation filters count for noindex, nofollow robots
     */
    const XML_PATH_NOINDEX_FOR_LN_COUNT        = 'mageworx_seo/base/robots/count_filters_for_noindex';

    /**
     * XML config path for noindex, follow if used category layered filter
     */
    const XML_PATH_NOINDEX_IF_CATEGORY_FILTER  = 'mageworx_seo/base/robots/category_filter_to_noindex';

    /**
     * XML config path "next" and "prev" link relations enabled
     */
    const XML_PATH_USE_NEXT_PREV               = 'mageworx_seo/base/use_next_prev';

    /**
     * Check if hreflangs enabled
     *
     * @param int $storeId
     * @return boolean
     */
    public function isHreflangsEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if hreflangs enabled for products
     *
     * @param int $storeId
     * @return boolean
     */
    public function isProductHreflangsEnabled($storeId)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_PRODUCT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if hreflangs enabled for categories
     *
     * @param int $storeId
     * @return boolean
     */
    public function isCategoryHreflangsEnabled($storeId)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_CATEGORY_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if hreflangs enabled for CMS pages
     *
     * @param int $storeId
     * @return boolean
     */
    public function isCmsPageHreflangsEnabled($storeId)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_CMS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if hreflangs enabled for pass type
     *
     * @param string $type
     * @param int $storeId
     * @return boolean
     */
    public function isHreflangsEnabledFor($type, $storeId)
    {
        if (!$this->isHreflangsEnabled($storeId)) {
            return false;
        }

        switch ($type) {
            case 'product':
                return $this->isProductHreflangsEnabled($storeId);
            case 'category':
                return $this->isCategoryHreflangsEnabled($storeId);
            case 'cms':
                return $this->isCmsPageHreflangsEnabled($storeId);
        }
        return true;
    }

    /**
     * Retrieve hreflangs scope setting
     *
     * @return int
     */
    public function getHreflangScope()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_SCOPE
        );
    }

    /**
     * Retrieve code of CMS Page relation method
     *
     * @return int
     */
    public function getCmsPageRelationWay()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_CMS_RELATION_WAY
        );
    }

    /**
     * Check if isset language code used
     *
     * @param int $storeId
     * @return boolean
     */
    public function isUseMagentoLanguageCode($storeId)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_USE_MAGENTO_LANGUAGE_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int $storeId
     * @return string
     */
    public function getLanguageCode($storeId)
    {
        if ($this->isUseMagentoLanguageCode($storeId)) {
            return $this->getLanguageCodeFromLocale($storeId);
        }

        return $this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_LANGUAGE_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve language code from magento locale code
     *
     * @param int $storeId
     * @return string
     */
    protected function getLanguageCodeFromLocale($storeId)
    {
        $locale = $this->scopeConfig->getValue(
            self::XML_PATH_MAGENTO_LANGUAGE_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        list($magentoLangCode) = explode('_', $locale);
        return $magentoLangCode;
    }

    /**
     *
     * @param int $storeId
     * @return boolean
     */
    public function isCountryCodeEnabled($storeId)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_COUNTRY_CODE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int $storeId
     * @return boolean
     */
    public function isUseMagentoCountryCode($storeId)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_USE_MAGENTO_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int $storeId
     * @return string
     */
    public function getCountryCode($storeId)
    {
        if ($this->isUseMagentoCountryCode($storeId)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_MAGENTO_COUNTRY_CODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * Retrieve hreflang x-default store id
     *
     * @return int
     */
    public function getXDefaultStoreIdForGlobalScope()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE
        );
    }

    /**
     * Retrieve hreflang x-default store ids
     *
     * @return array
     */
    public function getXDefaultStoreIdsForWebsiteScope()
    {
        $storeIdsAsString = (string)$this->scopeConfig->getValue(
            self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE
        );

        $xdefaultStoreIds = explode(',', $storeIdsAsString);
        if (empty($xdefaultStoreIds)) {
            return [];
        }
        return $xdefaultStoreIds;
    }

    /**
     *
     * @return array
     */
    public function getXDefaultStoreIds()
    {
        if ($this->getHreflangScope() == \MageWorx\SeoBase\Helper\Hreflangs::SCOPE_GLOBAL) {
            return [$this->getXDefaultStoreIdForGlobalScope()];
        }
        return $this->getXDefaultStoreIdsForWebsiteScope();
    }

    /**
     * Check if canonical URL enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function isCanonicalUrlEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CANONICAL_URL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve list of pages for noindex, follow robots
     *
     * @param int $storeId
     * @return bool
     */
    public function getCanonicalIgnorePages($storeId = null)
    {
        $ignorePagesString = $this->scopeConfig->getValue(
            self::XML_PATH_CANONICAL_IGNORE_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $ignorePages = array_filter(preg_split('/\r?\n/', $ignorePagesString));
        return array_map('trim', $ignorePages);
    }

    /**
     * Checks if add or crop trailing slash for URL
     *
     * @param int $storeId
     * @return int
     */
    public function getTrailingSlash($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TRAILING_SLASH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checks if add or crop trailing slash for home page URL
     *
     * @param int $storeId
     * @return int
     */
    public function getTrailingSlashForHomePage($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TRAILING_SLASH_FOR_HOME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if use pager for category canonical URL
     *
     * @param int $storeId
     * @return bool
     */
    public function usePagerForCanonical($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_USE_PAGER_FOR_CANONICAL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve canonical type for category layered navigation pages
     *
     * @param int $storeId
     * @return int
     */
    public function getCanonicalTypeForLayeredPages($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CANONICAL_FOR_LN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve cross domain store ID
     *
     * @param int $storeId
     * @return int
     */
    public function getCrossDomainStore($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CROSS_DOMAIN_STORE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve cross domain URL
     *
     * @param int $storeId
     * @return string
     */
    public function getCrossDomainUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSS_DOMAIN_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve comma-separated associated types for canonical URL
     *
     * @param int $storeId
     * @return string
     */
    public function getAssociatedProductTypes($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ASSOCIATED_TYPES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve list of associated types for canonical URL
     *
     * @param int $storeId
     * @return array
     */
    public function getAssociatedProductTypesAsArray($storeId = null)
    {
        return explode(',', $this->getAssociatedProductTypes($storeId));
    }

    /**
     * Retrieve robots for https protocol
     *
     * @param int $storeId
     * @return string
     */
    public function getMetaRobotsForHttps($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HTTPS_ROBOTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve list of pages for noindex, follow robots
     *
     * @param int $storeId
     * @return array
     */
    public function getNoindexPages($storeId = null)
    {
        $pagesString = $this->scopeConfig->getValue(
            self::XML_PATH_NOINDEX_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $arrayRaw = array_map('trim', explode(',', $pagesString));

        return array_filter($arrayRaw);
    }

    /**
     * Retrieve list of user pages for noindex, follow robots
     *
     * @param int $storeId
     * @return array
     */
    public function getNoindexUserPages($storeId = null)
    {
        $pagesString = $this->scopeConfig->getValue(
            self::XML_PATH_NOINDEX_USER_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $pagesArray = array_filter(preg_split('/\r?\n/', $pagesString));
        $pagesArray = array_map('trim', $pagesArray);
        return array_filter($pagesArray);
    }

    /**
     * Retrieve list of pages for noindex, nofollow robots
     *
     * @param int $storeId
     * @return array
     */
    public function getNoindexNofollowUserPages($storeId = null)
    {
        $pagesString = $this->scopeConfig->getValue(
            self::XML_PATH_NOINDEX_NOFOLLOW_USER_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $pagesArray = array_filter(preg_split('/\r?\n/', $pagesString));
        $pagesArray = array_map('trim', $pagesArray);
        return array_filter($pagesArray);
    }

    /**
     * Retrieve filter count for noindex pages
     *
     * @param int $storeId
     * @return mixed
     */
    public function getCountFiltersForNoindex($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NOINDEX_FOR_LN_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve if set NOINDEX, FOLLOW if category filter exists
     *
     * @param int $storeId
     * @return boolean
     */
    public function isUseNoindexIfCategoryFilter($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_NOINDEX_IF_CATEGORY_FILTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve CMS page home identifier code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHomeIdentifier($storeId = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check id "next" and "prev" link relations are enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function useNextPrev($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USE_NEXT_PREV,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
