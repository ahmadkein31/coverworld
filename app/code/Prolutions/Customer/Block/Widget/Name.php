<?php

namespace Prolutions\Customer\Block\Widget;

class Name extends \Magento\Customer\Block\Widget\Name
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param AddressHelper $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param \Magento\Customer\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata,
        \Magento\Customer\Model\Options $options,
        \Magento\Customer\Api\AddressMetadataInterface $addressMetadata,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_customerSession = $session;
        parent::__construct($context,
        $addressHelper,
        $customerMetadata,
        $options,
        $addressMetadata, $data);
    }

    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
}