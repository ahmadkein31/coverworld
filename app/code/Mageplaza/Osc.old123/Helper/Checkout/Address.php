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
namespace Mageplaza\Osc\Helper\Checkout;

use Mageplaza\Osc\Helper\Config;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

class Address extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $_modelSession;

    /**
     * @var Onepage
     */
    protected $_typeOnepage;


    /**
     * @var Shipping
     */
    protected $_checkoutShipping;

    /**
     * @var Config
     */
    protected $_helperConfig;

    public function __construct(Context $context,
                                Session $modelSession,
                                Onepage $typeOnepage,
                                Shipping $checkoutShipping,
                                Config $helperConfig)
    {
        $this->_modelSession          = $modelSession;
        $this->_typeOnepage           = $typeOnepage;
        $this->_checkoutShipping      = $checkoutShipping;
        $this->_helperConfig          = $helperConfig;

        parent::__construct($context);
    }


    const TEMPLATE_PATH = 'mageplaza/osc/';
    const EVENT_PREFIX = 'mageplaza_one_step_checkout_';

    public function getQuote()
    {
        return $this->_modelSession->getQuote();
    }

    public function getOnepage()
    {
        return $this->_typeOnepage;
    }

    public function validateAddressData($data)
    {
        $validationErrors = [];
        $requiredFields   = [
            'country_id',
            'city',
            'postcode',
            'region_id',
        ];
        foreach ($requiredFields as $requiredField) {
            if (!isset($data[$requiredField])) {
                $validationErrors[] = __("Field %s is required", $requiredField);
            }
        }

        return $validationErrors;
    }

    public function setDefaultShippingMethod($address)
    {
        $shippingRates = $address->setCollectShippingRates(true)->collectShippingRates()->getAllShippingRates();
        if (count($shippingRates) == 1) {
            $shippingMethod = $shippingRates[0]->getCode();
        } elseif (count($shippingRates) > 1) {
            $lastShippingMethod = $this->_checkoutShipping->getLastShippingMethod();
            if ($lastShippingMethod && $address->getShippingRateByCode($lastShippingMethod)) {
                $shippingMethod = $lastShippingMethod;
            } elseif ($this->_helperConfig->getDefaultShippingMethod()) {
                $shippingMethod = $this->_helperConfig->getDefaultShippingMethod();
            } else {
                $shippingMethod = $shippingRates[0]->getCode();
            }
        }
        if (isset($shippingMethod)) {
            $this->getOnepage()->saveShippingMethod($shippingMethod);
            $this->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
        }

        return $this;
    }


}