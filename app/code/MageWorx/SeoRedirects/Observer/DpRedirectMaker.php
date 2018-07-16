<?php

namespace MageWorx\SeoRedirects\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use MageWorx\SeoRedirects\Model\ResourceModel\Redirect\DpRedirect\CollectionFactory as DpRedirectCollectionFactory;
use MageWorx\SeoRedirects\Model\Redirect\DpRedirect;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Stdlib\DateTime\DateTime;
use MageWorx\SeoRedirects\Helper\DpRedirect\Data as HelperData;
use Magento\Framework\App\ResponseFactory;


class DpRedirectMaker implements \Magento\Framework\Event\ObserverInterface
{
    protected $_priorityCategories = [];

    protected $_productNames = [];

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var DpRedirectCollectionFactory
     */
    protected $dpRedirectCollectionFactory;

    /**
     * @var DpRedirect
     */
    protected $dpRedirect;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var CategoryCollection
     */
    protected $categoryCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateModel;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    public function __construct(
        \Magento\UrlRewrite\Model\UrlFinderInterface  $urlFinder,
        DpRedirectCollectionFactory $dpRedirectCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        ResourceProduct $resourceProduct,
        DateTime $dateModel,
        HelperData $helperData,
        ResponseFactory $responseFactory
    ) {
        $this->urlFinder = $urlFinder;
        $this->dpRedirectCollectionFactory = $dpRedirectCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->resourceProduct = $resourceProduct;
        $this->dateModel = $dateModel;
        $this->helperData = $helperData;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();

        if (!$request) {
            return;
        }

        if (!$this->helperData->isEnabled()) {
            return;
        }

        $storeId   = $this->storeManager->getStore()->getStoreId();
        $storeCode = $this->storeManager->getStore()->getStoreCode();

        list($requestUrlRaw) = explode('?', $request->getRequestUri());
        $requestUrl = ltrim(ltrim(ltrim($requestUrlRaw, '/'), $storeCode), '/');

        $redirectCollection = $this->dpRedirectCollectionFactory->create();
        $redirectCollection->addRequestPathsFilter([$requestUrl, rtrim($requestUrl, '/') . '/']);
        $redirectCollection->addStoreFilter($storeId);

        /**
         * @var \MageWorx\SeoRedirects\Model\Redirect\Product
         */
        $redirect = $redirectCollection->load()->getFirstItem();

        if (!empty($redirect['category_id'])) {
            $categoryIds = array_unique([$redirect['category_id'], $redirect['priority_category_id']]);

            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToFilter('entity_id', $categoryIds);
            $collection->addFieldToFilter('is_active', ['eq' => 1]);
            $collection->setStoreId($storeId);

            $category = $this->_getCategoryModel($collection, $redirect);

            if ($category) {
                $redirect->setHits($redirect->getHits() + 1);
                $redirect->save();
                $redirectCode = $this->helperData->getRedirectType();

                $this->responseFactory->create()->setRedirect($category->getUrl(), $redirectCode)->sendResponse();
                exit;
            }
        }

        return;
    }


    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection\ $collection
     * @param \MageWorx\SeoRedirects\Model\Redirect\DpRedirect $redirect
     * @return \Magento\Catalog\Model\Category
     */
    protected function _getCategoryModel($collection, $redirect)
    {
        if ($collection->count() < 2) {
            return $collection->getFirstItem();
        }

        $isUsePriorityCategory = $this->helperData->isForceProductRedirectByPriority();

        foreach ($collection as $item) {
            if ($isUsePriorityCategory ) {
                if ($redirect['priority_category_id'] == $item->getId()) {
                    return $item;
                }
            } else {
                if ($redirect['category_id'] == $item->getId()) {
                    return $item;
                }
            }
        }
    }
}