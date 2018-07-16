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
namespace Mageplaza\Osc\Block\Container\Review;

use Mageplaza\Osc\Block\Container\Review;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Mageplaza\Osc\Block\Container;
use Mageplaza\Osc\Block\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;

class Term extends Review
{

    protected $_agreementCollectionFactory;
    protected $_catalogHelper;

    public function __construct(
        Context $context,
        AgreementCollectionFactory $agreementCollectionFactory,
        CatalogHelper $catalogHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_agreementCollectionFactory = $agreementCollectionFactory;
        $this->_catalogHelper              = $catalogHelper;


    }

    /**
     * enable gift message or not
     *
     */
    public function canShow()
    {
        return count($this->getTermAndConditions());
    }

    public function getTermAndConditions()
    {
        $agreements = [];
        if ($this->_helperConfig->isEnabledTerm()) {
            $agreementsDefault = $this->getAgreements();
            foreach ($agreementsDefault as $agree) {
                $agreements[] = $agree;
            }
            $content      = $this->_helperConfig->getTermContent();
            $checkboxText = $this->_helperConfig->getTermCheckboxText();
            $checkboxName = $this->_helperConfig->getTermTitle();
            if ($checkboxText && $checkboxName && $content) {
                $agreementConfig = [
                    'id'            => 'mc_osc_term',
                    'checkbox_text' => $checkboxText,
                    'name'          => $checkboxName,
                    'content'       => $this->_process($content),
                    'is_html'       => true
                ];
                $agreements[]    = new DataObject($agreementConfig);
            }
        }

        return $agreements;
    }

    public function getAgreements()
    {
        if (!$this->hasAgreements()) {
            $agreements = [];
            if ($this->_scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE)) {
                /** @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection $agreements */
                $agreements = $this->_agreementCollectionFactory->create();
                $agreements->addStoreFilter($this->_storeManager->getStore()->getId());
                $agreements->addFieldToFilter('is_active', 1);
            }
            $this->setAgreements($agreements);
        }

        return $this->getData('agreements');
    }

    protected function _process($text)
    {
        $helper    = $this->_catalogHelper;
        $processor = $helper->getPageTemplateProcessor();
        $processor->setVariables($this->_getVariables());

        return $processor->filter($text);
    }

    private function _getVariables()
    {
        $variables = [];
        if ($productId = $this->getData('product_id')) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            if (!is_null($product->getId())) {
                $variables['product'] = $product;
            }
        }

        return $variables;
    }

    public function isRequiredReadTerm()
    {
        return $this->_helperConfig->isRequiredReadTerm();
    }

}