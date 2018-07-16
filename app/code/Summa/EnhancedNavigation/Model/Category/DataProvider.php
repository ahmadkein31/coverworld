<?php

/**
 * Class DataProvider
 *
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

namespace Summa\EnhancedNavigation\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Catalog\Model\CategoryFactory;

class DataProvider
    extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @var \Summa\EnhancedNavigation\Helper\UrlInterface
     */
    protected $iconUrlHelper;

    public function __construct(
        \Summa\EnhancedNavigation\Helper\UrlInterface $iconUrlHelper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        Config $eavConfig,
        \Magento\Framework\App\RequestInterface $request,
        CategoryFactory $categoryFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $eavValidationRules,
            $categoryCollectionFactory, $storeManager, $registry, $eavConfig, $request, $categoryFactory, $meta, $data);

        $this->iconUrlHelper = $iconUrlHelper;
    }

    protected function getFieldsMap()
    {
        $fieldsMap = parent::getFieldsMap();
        $fieldsMap['content'][] = 'navigation_banner';
        $fieldsMap['content'][] = 'icon_image';

        return $fieldsMap;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();
            $categoryData = $this->addUseDefaultSettings($category, $categoryData);
            $categoryData = $this->addUseConfigSettings($categoryData);
            $categoryData = $this->filterFields($categoryData);
            if (isset($categoryData['image'])) {
                unset($categoryData['image']);
                $categoryData['image'][0]['name'] = $category->getData('image');
                $categoryData['image'][0]['url'] = $category->getImageUrl();
            }
            if (isset($categoryData['icon_image'])) {
                unset($categoryData['icon_image']);
                $categoryData['icon_image'][0]['name'] = $category->getData('icon_image');
                $categoryData['icon_image'][0]['url'] = $this->iconUrlHelper->getIconUrl($category->getData('icon_image'));
            }
            $this->loadedData[$category->getId()] = $categoryData;
        }
        return $this->loadedData;
    }
}