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
namespace Mageplaza\Osc\Block\Container\Payment\Method;

use Mageplaza\Osc\Block\Container\Payment\Method;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Mageplaza\Osc\Block\Context;

class Available extends Method
{

    protected $_methodSpecificationFactory;
    protected $_paymentHelper;

    public function __construct(
        Context $context,
        array $data = [],
        PaymentHelper $paymentHelper,
        SpecificationFactory $methodSpecificationFactory
    ) {

        parent::__construct($context, $data);
        $this->_paymentHelper              = $paymentHelper;
        $this->_methodSpecificationFactory = $methodSpecificationFactory;
    }


    /**
     * Retrieve available payment methods
     *
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote         = $this->getQuote();
            $store         = $quote ? $quote->getStoreId() : null;
            $methods       = [];
            $specification = $this->_methodSpecificationFactory->create([AbstractMethod::CHECK_ZERO_TOTAL]);
            foreach ($this->_paymentHelper->getStoreMethods($store, $quote) as $method) {
                if ($this->_canUseMethod($method) && $specification->isApplicable($method, $this->getQuote())) {
                    $this->_assignMethod($method);
                    $methods[] = $method;
                }
            }

            $this->setData('methods', $methods);
        }

        return $methods;
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     *
     * @return bool
     */
    protected function _canUseMethod(\Magento\Payment\Model\MethodInterface $method)
    {
        $methodSpecification = $this->_methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        );

        return $method && $method->canUseCheckout() && $methodSpecification->isApplicable($method, $this->getQuote());
    }

    protected function _assignMethod(\Magento\Payment\Model\MethodInterface $method)
    {
        $method->setInfoInstance($this->getQuote()->getPayment());

        return $this;
    }


    /**
     * Payment method form html getter.
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     *
     * @return string
     */
    public function getPaymentMethodFormHtml(\Magento\Payment\Model\MethodInterface $method)
    {
        return $this->getChildHtml('payment.method.' . $method->getCode());
    }

    /**
     * @param \Magento\Payment\Model\MethodInterface $method
     * @return string
     */
    public function getMethodTitle(\Magento\Payment\Model\MethodInterface $method)
    {
        $form = $this->getChildHtml('payment.method.' . $method->getCode());
        if ($form && $form->hasMethodTitle()) {
            return $form->getMethodTitle();
        }

        return $method->getTitle();
    }

    /**
     * @param \Magento\Payment\Model\MethodInterface $method
     * @return null
     */
    public function getMethodImage(\Magento\Payment\Model\MethodInterface $method)
    {
        $form = $this->getChildBlock('payment.method.' . $method->getCode());
        if ($form && $form->hasMethodImage()) {
            return $form->getMethodImage();
        }

        return null;
    }

    /**
     * Payment method additional label part getter.
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     *
     * @return mixed
     */
    public function getMethodLabelAfterHtml(\Magento\Payment\Model\MethodInterface $method)
    {
        if ($form = $this->getChild('payment.method.' . $method->getCode())) {
            return $form->getMethodLabelAfterHtml();
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public function isChecked($code)
    {
        if ($currentCode = $this->getQuote()->getPayment()->getMethod()) {
            return $currentCode == $code;
        } else {
            return $code == $this->getHelperConfig()->getDefaultPaymentMethod();
        }
    }

}