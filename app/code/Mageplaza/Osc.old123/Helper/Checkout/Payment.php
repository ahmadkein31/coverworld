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
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Payment\Helper\Data as PaymentHelper;

class Payment extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $_checkoutSession;
    protected $_customerSession;

    /**
     * @var Onepage
     */
    protected $_typeOnepage;

    /**
     * @var ManagerInterface
     */

    /**
     * @var Shipping
     */
    protected $_checkoutShipping;

    /**
     * @var Config
     */
    protected $_helperConfig;
    protected $_paymentHelper;

    public function __construct(Context $context,
                                CheckoutSession $checkoutSession,
                                CustomerSession $customerSession,
                                Onepage $typeOnepage,
                                Shipping $checkoutShipping,
                                Config $helperConfig,
                                PaymentHelper $paymentHelper)
    {
        $this->_checkoutSession       = $checkoutSession;
        $this->_customerSession       = $customerSession;
        $this->_typeOnepage           = $typeOnepage;
        $this->_checkoutShipping      = $checkoutShipping;
        $this->_helperConfig          = $helperConfig;
        $this->_paymentHelper         = $paymentHelper;
        parent::__construct($context);
    }


    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    public function getOnepage()
    {
        return $this->_typeOnepage;
    }

    public function getLastPaymentMethod()
    {
        $customer = $this->_customerSession->getCustomer();
        if (!$customer->getId()) {
            return false;
        }
        $collection = ObjectManager::getInstance()->create('Magento\Sales\Model\Order')
            ->getCollection()
            ->addFilter('customer_id', $customer->getId())
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize(1);

        $lastOrder = $collection->getFirstItem();
        if (!$lastOrder->getId()) {
            return false;
        }

        return $lastOrder->getPayment()->getMethod();
    }

    /**
     * Set
     */
    public function setDefaultPaymentMethod()
    {
        if (!$this->getQuote()->getPayment()->getMethod()) {
            if (!empty($data = $this->getDefaultPaymentMethod())) {
                try {
                    $this->getOnepage()->savePayment($data);
                    $this->getQuote()->collectTotals()->save();
                } catch (\Exception $e) {
                    // catch this exception
                }
            }
        }
    }

    public function getDefaultPaymentMethod()
    {
        $data = [];
        if ($method = $this->getQuote()->getPayment()->getMethod()) {
            $data['method'] = $method;
        } else {
            $paymentMethods = $this->_paymentHelper->getPaymentMethods();
            if ((count($paymentMethods) == 1)) {
                $currentPaymentMethod = current($paymentMethods);
                $data['method']       = $currentPaymentMethod->getCode();
            } elseif ($lastPaymentMethod = $this->getLastPaymentMethod()) {
                $data['method'] = $lastPaymentMethod;
            } elseif ($defaultPaymentMethod = $this->_helperConfig->getDefaultPaymentMethod()) {
                $data['method'] = $defaultPaymentMethod;
            }
        }


        return $data;
    }


}