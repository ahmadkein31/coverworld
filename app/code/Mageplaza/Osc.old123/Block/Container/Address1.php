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
namespace Mageplaza\Osc\Block\Container;

use Mageplaza\Osc\Block\Container;

class Address extends Container
{
    protected $_attributes;

    /**
     * @return bool|int
     */
    public function allowShipToDifferentChecked()
    {
        if ($address = $this->getQuote()->getShippingAddress()) {

            return $this->getQuote()->getShippingAddress()->getData('same_as_billing');
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function allowShipToDifferent()
    {
        return $this->_helperConfig->allowShipToDifferent();
    }

    /**
     * @return bool
     */
    public function customerMustBeRegistered()
    {
        return $this->isCustomerLoggedIn()
        || $this->_helperConfig->allowGuestCheckout($this->getQuote()->getStoreId());
    }

    /**
     * @param $type
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    /**
     * Customer Taxvat Widget block
     *
     * @var \Magento\Customer\Block\Widget\Taxvat
     */

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = [];
            foreach ($this->getCustomerAddresses() as $address) {
                $options[] = [
                    'value' => $address->getId(),
                    'label' => $address->format('oneline'),
                ];
            }
            $addressId         = $this->getAddress()->getId();
            $shippingAddressId = $this->getCustomer()->getDefaultShipping();

            if ($shippingAddressId != $addressId && $type == 'shipping') {
                $addressId = $shippingAddressId;
            }

            if (empty($addressId)) {
                if ($type == 'billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            /** @var \Magento\Framework\View\Element\Html\Select $select */
            $select = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
                ->setName($type . '_address_id')
                ->setId($type . '-address-select')
                ->setClass('address-select')
                ->setExtraParams('style="width:350px"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', __('New Address'));

            return $select->getHtml();
        }

        return '';
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getCustomerAddresses()
    {
        return $this->getCustomer()->getAddresses();
    }

    public function getWidgetHtml($name, $type = null, $template = null, $fieldOption = null)
    {
        switch ($name) {
            case 'name':
                if ($type == 'billing') {
                    $html = $this->getLayout()
                        ->createBlock('Magento\Customer\Block\Widget\Name')
                        ->setTemplate('Mageplaza_Osc::container/address/customer/widget/' . $template . '.phtml')
                        ->setObject($this->_getObjectForCustomerNameWidget())
                        ->setForceUseCustomerRequiredAttributes(!$this->isCustomerLoggedIn())
                        ->setFieldIdFormat('billing:%s')
                        ->setFieldNameFormat('billing[%s]')
                        ->setFieldOption($fieldOption)
                        ->toHtml();
                } else {
                    $html = $this->getLayout()
                        ->createBlock('Magento\Customer\Block\Widget\Name')
                        ->setTemplate('Mageplaza_Osc::container/address/customer/widget/' . $template . '.phtml')
                        ->setObject($this->_getObjectForCustomerNameWidget())
                        ->setFieldIdFormat('shipping:%s')
                        ->setFieldNameFormat('shipping[%s]')
                        ->setFieldParams('onchange="shipping.setSameAsBilling(false)"')
                        ->setFieldOption($fieldOption)
                        ->toHtml();
                }

                break;
            case 'dob':
                $html = $this->getLayout()
                    ->createBlock('Magento\Customer\Block\Widget\Dob')
                    ->setDate($this->_getDateForDOBWidget())
                    ->setFieldIdFormat('billing:%s')
                    ->setFieldNameFormat('billing[%s]')
                    ->toHtml();
                break;
            case 'gender':
                $genderBlock = $this->getLayout()
                    ->createBlock('Magento\Customer\Block\Widget\Gender')
                    ->setGender($this->getBillingDataFromSession('gender'))
                    ->setFieldIdFormat('billing:%s')
                    ->setFieldNameFormat('billing[%s]');
                if ($genderBlock->isEnabled())
                    $html = $genderBlock->toHtml();
                else $html = '';
                break;
            case 'country':
                $countryId = $this->getBillingDataFromSession('country_id');
                if (is_null($countryId)) {
                    $countryId = $this->_helperConfig->getDefaultCountryId();
                }
                $countryBlock = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
                    ->setName($type . '[country_id]')
                    ->setId($type . ':country_id')
                    ->setTitle(__('Country'))
                    ->setClass('validate-select')
                    ->setValue($countryId)
                    ->setOptions($this->getCountryOptions());
                $html         = $countryBlock->getHtml();                         
                break;
            case 'taxvat':
                $html = $this->getCustomerWidgetTaxvat()
                    ->setTaxvat($this->getDataFromSession('taxvat'))
                    ->setFieldIdFormat('billing:%s')
                    ->setFieldNameFormat('billing[%s]')
                    ->toHtml();
                break;
            default:
                $html = '';

        }

        return $html;
    }

    /**
     * @return mixed
     */
    public function getCountryOptions()
    {
        $countries = $this->_objectManager->create('Magento\Directory\Model\Config\Source\Country');
        
        return $countries->toOptionArray();
    }

    /**
     * get
     *
     * @param $path
     * @return null
     */
    public function getBillingDataFromSession($path)
    {
        $formData = $this->_checkoutSession->getData('osc_form_values/billing');
        if (!empty($formData[$path])) {
            return $formData[$path];
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function _getObjectForCustomerNameWidget()
    {
        $formData = $this->_checkoutSession->getData('osc_form_values');
        $address  = $this->getAddress();
        if (isset($formData['billing'])) {
            $address->addData($formData['billing']);
        }
        if ($address->getFirstname() || $address->getLastname()) {
            return $address;
        }

        return $this->getQuote()->getCustomer();
    }

    public function getAddress()
    {
        if ($this->isCustomerLoggedIn()) {
            $customerAddressId = $this->getCustomer()->getDefaultBilling();
            if ($customerAddressId) {
                $billing = $this->_objectManager->create('Magento\Customer\Model\Address')->load($customerAddressId);
            } else {
                $billing = $this->getQuote()->getBillingAddress();
            }

            if (!$billing->getCustomerAddressId()) {
                $customer              = $this->getCustomer();
                $defaultBillingAddress = $customer->getDefaultBillingAddress();

                if ($defaultBillingAddress && $defaultBillingAddress->getId()) {
                    $billing->setCustomerAddressId($defaultBillingAddress->getId())->save();
                } else {
                    return $billing;
                }
            }

            return $billing;
        } else {
            return $this->_objectManager->get('Magento\Quote\Model\Quote\Address');
        }
    }

    /**
     * @return int
     */
    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }

    /**
     * get Attributes position and order to view in frontend
     */
    public function getAttributes()
    {
        if (!$this->_attributes) {
            $this->_attributes = $this->_objectManager->create('Mageplaza\Osc\Model\Attribute')->getSortedFields();

        }

        return $this->_attributes;
    }

    public function getBillingTriggerElements()
    {
        $triggers = [];
        foreach ($this->getAddressTriggerElements() as $element) {
            $triggers[] = 'billing:' . $element;
        }

        return $this->_jsonHelper->jsonEncode($triggers);
    }

    public function getAddressTriggerElements()
    {
        $triggers = ['street1', 'city', 'country_id','region_id', 'postcode', 'telephone'];

        return $triggers;
    }

    public function getShippingTriggerElements()
    {
        $triggers = [];
        foreach ($this->getAddressTriggerElements() as $element) {
            $triggers[] = 'shipping:' . $element;
        }

        return $this->_jsonHelper->jsonEncode($triggers);
    }


    public function getChangeAddressUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/saveAddressTrigger', ['_secure' => $this->isSecure()]);
    }
}