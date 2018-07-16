<?php

namespace Prolutions\Quote\Model;

class BillingAddressManagement extends \Magento\Quote\Model\BillingAddressManagement
{
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address, $useForShipping = false)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setBillingAddress($address);
        try {
            $quote->setDataChanges(true);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to save address. Please check input data.'));
        }
        return $quote->getBillingAddress()->getId();
    }
}