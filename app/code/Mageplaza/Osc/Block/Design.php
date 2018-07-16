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
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Osc\Block;

use Mageplaza\Osc\Helper\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Css
 * @package Mageplaza\Osc\Block\Generator
 */
class Design extends Template
{
    /**
     * @var Config
     */
    protected $_helperConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\Osc\Helper\Config $helperConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $helperConfig,
        array $data = []
    ) {
    
        parent::__construct($context, $data);

        $this->_helperConfig = $helperConfig;
    }

    /**
     * @return \Mageplaza\Osc\Helper\Config
     */
    public function getHelperConfig()
    {
        return $this->_helperConfig;
    }

    /**
     * @return bool
     */
    public function isEnableGoogleApi()
    {
        return $this->getHelperConfig()->getAutoDetectedAddress() == 'google';
    }

    /**
     * @return mixed
     */
    public function getGoogleApiKey()
    {
        return $this->getHelperConfig()->getGoogleApiKey();
    }

    /**
     * @return array
     */
    public function getDesignConfiguration()
    {
        return $this->getHelperConfig()->getDesignConfig();
    }
}
