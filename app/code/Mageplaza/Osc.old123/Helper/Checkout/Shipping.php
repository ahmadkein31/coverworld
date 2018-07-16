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

use Magento\Checkout\Model\Session as ModelSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\Db;

class Shipping extends AbstractHelper
{
    /**
     * @var ManagerInterface
     */

    /**
     * @var Session
     */
    protected $_modelSession;

    /**
     * @var ModelSession
     */
    protected $_checkoutModelSession;

    /**
     * @var Onepage
     */
    protected $_typeOnepage;

    public function __construct(Context $context,
                                Session $modelSession,
                                ModelSession $checkoutModelSession,
                                Onepage $typeOnepage)
    {
        $this->_modelSession          = $modelSession;
        $this->_checkoutModelSession  = $checkoutModelSession;
        $this->_typeOnepage           = $typeOnepage;

        parent::__construct($context);
    }

    const TEMPLATE_PATH = 'mageplaza/osc/';
    const EVENT_PREFIX = 'mageplaza_one_step_checkout_';

    /**
     * get shippimg method temple
     *
     * @return string
     */
    public function getShippingMethodTemplate()
    {
        $template = new DataObject([
            'file_path' => 'checkout/shipping.phtml'
        ]);
        $this->_eventManager->dispatch(self::EVENT_PREFIX . 'get_shipping_method_template_before', [
            'template' => $template
        ]);

        return self::TEMPLATE_PATH . $template->getFilePath();
    }

    public function getShippingRates()
    {
        $address       = $this->getQuote()->getShippingAddress()->collectShippingRates()
            ->save();
        $shippingRates = $address->getGroupedAllShippingRates();

        return $shippingRates;
    }


    public function getLastShippingMethod()
    {
        $customer = $this->_modelSession->getCustomer();
        if (!$customer || !$customer->getId()) {
            return false;
        }
        $lastOrder = $this->getOrderCollection($customer)->getFirstItem();
        if (!$lastOrder || !$lastOrder->getId()) {
            return false;
        }

        return $lastOrder->getShippingMethod();
    }

    public function getQuote()
    {
        return $this->_checkoutModelSession
            ->getQuote();
    }

    public function getOrderCollection($customer)
    {
        $orderCollection = ObjectManager::getInstance()->create('Magento\Sales\Model\Order')
            ->getCollection()
            ->addFieldToFilter('shipping_method', ['neq' => ''])
            ->addFilter('customer_id', $customer->getId())
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize(1);

        return $orderCollection;
    }

    /**
     * @return Onepage
     */
    public function getOnepage()
    {
        return $this->_typeOnepage;
    }
}