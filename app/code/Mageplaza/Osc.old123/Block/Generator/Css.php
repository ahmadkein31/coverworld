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
namespace Mageplaza\Osc\Block\Generator;

use Mageplaza\Osc\Helper\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Mageplaza\Osc\Block\Context;

class Css extends Template
{
    /**
     * @var Config
     */
    protected $_helperConfig;

    protected $_objectManager;

    public function __construct(Context $context,
                                array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helperConfig  = $context->getHelperConfig();
        $this->_objectManager = $context->getObjectManager();

    }

    public function getFieldColspan($attribute_code)
    {
        return 2;
    }

    public function getHelperConfig()
    {
        return $this->_helperConfig;
    }
}