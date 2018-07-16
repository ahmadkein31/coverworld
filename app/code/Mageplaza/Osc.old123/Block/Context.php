<?php

/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Mageplaza
 * @package    Mageplaza_Osc
 * @version    3.0.0
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Osc\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\Form\FormKey;
use Mageplaza\Osc\Helper\Data as HelperData;
use Mageplaza\Osc\Helper\Config as HelperConfig;
use Mageplaza\Osc\Helper\Block as HelperBlock;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Checkout\Model\CompositeConfigProvider;

class Context extends TemplateContext
{
    protected $_objectManager;
    protected $_moduleManager;
    protected $_helperData;
    protected $_helperConfig;
    protected $_helperBlock;
    protected $_jsonHelper;
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_formKey;
    protected $_configProvider;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\View\TemplateEnginePool $enginePool,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Element\Template\File\Resolver $resolver,
        \Magento\Framework\View\Element\Template\File\Validator $validator,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager,
        HelperData $helperData,
        HelperConfig $helperConfig,
        HelperBlock $helperBlock,
        JsonHelperData $jsonHelper,
        CustomerSession $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        CompositeConfigProvider $configProvider,
        FormKey $formKey
    ) {
        parent::__construct(
            $request,
            $layout,
            $eventManager,
            $urlBuilder,
            $cache,
            $design,
            $session,
            $sidResolver,
            $scopeConfig,
            $assetRepo,
            $viewConfig,
            $cacheState,
            $logger,
            $escaper,
            $filterManager,
            $localeDate,
            $inlineTranslation,
            $filesystem,
            $viewFileSystem,
            $enginePool,
            $appState,
            $storeManager,
            $pageConfig,
            $resolver,
            $validator
        );
        $this->_objectManager   = $objectManager;
        $this->_moduleManager   = $moduleManager;
        $this->_helperData      = $helperData;
        $this->_helperConfig    = $helperConfig;
        $this->_helperBlock     = $helperBlock;
        $this->_jsonHelper      = $jsonHelper;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_configProvider  = $configProvider;
        $this->_formKey         = $formKey;
    }

    public function getConfigProvider()
    {
        return $this->_configProvider;
    }

    /**
     * @return \Magento\Framework\Module\Manager
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }

    /**
     * @return \Magento\Framework\Module\Manager
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }

    /**
     * get secure checkout helper data
     *
     * @return HelperData
     */
    public function getHelperData()
    {
        return $this->_helperData;
    }

    /**
     * get Secure Checkout helper config
     *
     * @return HelperConfig
     */
    public function getHelperConfig()
    {
        return $this->_helperConfig;
    }

    /**
     * @return HelperBlock
     */
    public function getHelperBlock()
    {
        return $this->_helperBlock;
    }

    /**
     * @return JsonHelperData
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    /**
     * @return CustomerSession
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }


}