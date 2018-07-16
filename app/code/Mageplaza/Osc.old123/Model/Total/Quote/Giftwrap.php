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

/**
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @author      Mageplaza Developer
 */
namespace Mageplaza\Osc\Model\Total\Quote;

use Mageplaza\Osc\Helper\Checkout\Review\Giftwrap as GiftWrapHelper;
use Mageplaza\Osc\Helper\Data as HelperData;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class Giftwrap extends AbstractTotal
{
    /**
     * @var GiftWrapHelper
     */
    protected $_giftWrapHelper;
    protected $_helperData;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var ManagerInterface
     */
    protected $_eventManagerInterface;

    public function __construct(
        GiftWrapHelper $giftWrapHelper,
        HelperData $helperData,
        Session $checkoutSession,
        ManagerInterface $eventManagerInterface)
    {
        $this->setCode('mc_gift_wrap');
        $this->_giftWrapHelper        = $giftWrapHelper;
        $this->_helperData            = $helperData;
        $this->_checkoutSession       = $checkoutSession;
        $this->_eventManagerInterface = $eventManagerInterface;

    }


    /**
     * collect reward points that customer earned (per each item and address) total
     *
     * @param Address $address
     * @param Quote   $quote
     * @return \Mageplaza\Osc\Model\Total\Quote\Point
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $_giftWrapHelper = $this->_giftWrapHelper;
        $session         = $this->_checkoutSession;
        if (!$_giftWrapHelper->isEnabled($quote->getStoreId()) || !$session->getData('is_used_giftwrap')) {
            return $this;
        }
        $giftWrapBaseAmount = $_giftWrapHelper->getGiftWrapAmount($quote);
        $giftWrapAmount     = $this->_helperData->convertPrice($giftWrapBaseAmount);
        if ($giftWrapAmount > 0) {
            $total->setMcGiftwrapBaseAmount($giftWrapBaseAmount);
            $total->setMcGiftwrapAmount($giftWrapAmount);
            $total->setBaseGrandTotal($total->getGrandTotal() + $giftWrapBaseAmount);
            $total->setGrandTotal($total->getGrandTotal() + $giftWrapAmount);
        }
        $this->_eventManagerInterface->dispatch('osc_collect_total_giftwrap_before', [
            'address' => $quote,
        ]);

        return $this;
    }

    /**
     * fetch
     *
     * @param Address $address
     * @return $this|array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getMcGiftwrapAmount();
        if ($amount != 0) {
            $result = [
                'code'  => $this->getCode(),
                'title' => __('Gift Wrap'),
                'value' => $amount,
            ];
        }

        return $result;
    }
}
