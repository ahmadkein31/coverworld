<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mageplaza\Osc\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\Osc\Helper\Data as HelperData;

class OrderPlaceAfter implements ObserverInterface
{

    protected $_checkoutSession;
    protected $_customerSession;
    protected $_storeManagerInterface;
    protected $_objectManagerInterface;
    protected $_helperData;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManagerInterface,
        ObjectManagerInterface $objectManagerInterface,
        HelperData $helperData
    ) {
        $this->_checkoutSession        = $checkoutSession;
        $this->_customerSession        = $customerSession;
        $this->_storeManagerInterface  = $storeManagerInterface;
        $this->_objectManagerInterface = $objectManagerInterface;
        $this->_helperData             = $helperData;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        if (!$event)
            return $this;
        $order     = $event->getOrder();
        $orderData = $this->_checkoutSession->getData('osc_order_data');
        if (empty($orderData)) {
            return $this;
        }
        // add customer comment
        if (array_key_exists('comments', $orderData) && ($orderData['comments']) != '') {
            $comment = $orderData['comments'];
            $order->setMcOrderCommment($comment);
            $order->addStatusHistoryComment($comment)
                ->setIsVisibleOnFront(true);
        }
        /*subscribe newsletter*/
        if (array_key_exists('is_subscribed', $orderData) && $orderData['is_subscribed']) {
            $customer         = $this->_customerSession->getCustomer();
            $data             = [];
            $data['store_id'] = $this->_storeManagerInterface->getStore()->getId();
            if (!$customer || !$customer->getId()) {
                $billing         = $orderData['billing'];
                $subscribedEmail = $billing['email'];
            } else {
                $subscribedEmail = $customer->getEmail();
            }
            if (!$this->_helperData->isSubscribed($subscribedEmail)) {
                $this->_helperData->getSubscriber()->subscribe($subscribedEmail);
            }
        }
        $this->clearAllSession();

        return $this;
    }


    /**
     */

    public function clearAllSession()
    {
        $this->_checkoutSession->setData('osc_form_values', []);
        $this->_checkoutSession->setData('osc_order_data', []);
        $this->_checkoutSession->setData('is_used_giftwrap', '');
        $this->_checkoutSession->setData('same_as_billing', '');
    }
}
