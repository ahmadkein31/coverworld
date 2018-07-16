<?php
/**
 * Class Url
 */

namespace Summa\EnhancedNavigation\Helper;

class Url
    implements UrlInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Url constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getIconUrl($iconImage)
    {
        $url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $url .= 'catalog/category/';
        $url .= $iconImage;

        return $url;
    }
}