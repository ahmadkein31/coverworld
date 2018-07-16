<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Feed\Model;

use Magento\Catalog\Model\Product;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Feed\Model\Resource\Feed');
        $this->setIdFieldName('entity_id');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Feed\Model\Rule\Condition\CombineFactory $combineFactory,
//        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {

        return parent::__construct($context,
                $registry,
                $formFactory,
                $localeDate,
                $productCollectionFactory,
                $storeManager,
                $combineFactory,
                $actionCollectionFactory,
                $productFactory,
                $resourceIterator,
                $customerSession,
                $catalogRuleData,
                $cacheTypesList,
                $dateTime,
                $ruleProductProcessor,
                $resource,
                $resourceCollection,
                $relatedCacheTypes,
                $data);
    }

    public function getFeedMatchingProductIds() //skip afterGetMatchingProductIds plugin
    {
        if ($this->_productIds === null) {

            $this->_productIds = [];
            $this->setCollectedAttributes([]);


            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();
            $productCollection->addStoreFilter($this->getStoreId());
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->_productFactory->create()
                ]
            );

        }

        return $this->_productIds;
    }

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $results = [];

        $product->setStoreId($this->getStoreId());

        $validate = $this->getConditions()->validate($product);

        if ($validate)
        {
            $results[$this->getStoreId()] = $validate;
            $this->_productIds[$product->getId()] = $results;

        }

    }
}