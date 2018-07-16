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
namespace Mageplaza\Osc\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\AccountManagement;
use Magento\GiftMessage\Model\CompositeConfigProvider;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Mageplaza\Osc\Helper\Config as OscConfig;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DefaultConfigProvider implements ConfigProviderInterface
{

	/**
	 * @var CheckoutSession
	 */
	private $checkoutSession;

	/**
	 * @var \Magento\Quote\Api\PaymentMethodManagementInterface
	 */
	protected $paymentMethodManagement;

	/**
	 * @type \Magento\Quote\Api\ShippingMethodManagementInterface
	 */
	protected $shippingMethodManagement;

	/**
	 * @type \Mageplaza\Osc\Helper\Config
	 */
	protected $oscConfig;

	/**
	 * @var \Magento\Checkout\Model\CompositeConfigProvider
	 */
	protected $giftMessageConfigProvider;

	/**
	 * @var ModuleManager
	 */
	protected $moduleManager;

	/**
	 * DefaultConfigProvider constructor.
	 * @param CheckoutSession $checkoutSession
	 * @param PaymentMethodManagementInterface $paymentMethodManagement
	 * @param ShippingMethodManagementInterface $shippingMethodManagement
	 * @param OscConfig $oscConfig
	 * @param CompositeConfigProvider $configProvider
	 * @param ModuleManager $moduleManager
	 */
	public function __construct(
		CheckoutSession $checkoutSession,
		PaymentMethodManagementInterface $paymentMethodManagement,
		ShippingMethodManagementInterface $shippingMethodManagement,
		OscConfig $oscConfig,
		CompositeConfigProvider $configProvider,
		ModuleManager $moduleManager
	)
	{
		$this->checkoutSession         				= $checkoutSession;
		$this->paymentMethodManagement  			= $paymentMethodManagement;
		$this->shippingMethodManagement 			= $shippingMethodManagement;
		$this->oscConfig                			= $oscConfig;
		$this->giftMessageConfigProvider 			= $configProvider;
		$this->moduleManager						= $moduleManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		if (!$this->oscConfig->isOscPage()) {
			return [];
		}

		$output = [
			'shippingMethods'       => $this->getShippingMethods(),
			'selectedShippingRate'  => $this->oscConfig->getDefaultShippingMethod(),
			'paymentMethods'        => $this->getPaymentMethods(),
			'selectedPaymentMethod' => $this->oscConfig->getDefaultPaymentMethod(),
			'oscConfig'             => $this->getOscConfig()
		];

		return $output;
	}

	/**
	 * @return array
	 */
	private function getOscConfig()
	{
		return [
			'addressFields'      	=> $this->getAddressFields(),
			'autocomplete'       	=> [
				'type'                   => $this->oscConfig->getAutoDetectedAddress(),
				'google_default_country' => $this->oscConfig->getGoogleSpecificCountry(),
			],
			'register'           	=> [
				'dataPasswordMinLength'        => $this->oscConfig->getConfigValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH),
				'dataPasswordMinCharacterSets' => $this->oscConfig->getConfigValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER)
			],
			'allowGuestCheckout' 	=> $this->oscConfig->getAllowGuestCheckout($this->checkoutSession->getQuote()),
			'showBillingAddress' 	=> $this->oscConfig->getShowBillingAddress(),
			'newsletterDefault' 	=> $this->oscConfig->isSubscribedByDefault(),
			'isUsedGiftWrap'     	=> (bool)$this->checkoutSession->getQuote()->getShippingAddress()->getUsedGiftWrap(),
			'giftMessageOptions' 	=> $this->giftMessageConfigProvider->getConfig(),
			'isDisplaySocialLogin'	=> $this->isDisplaySocialLogin(),
			'deliveryTimeOptions'	=> [
				'deliveryTimeFormat'		=> $this->oscConfig->getDeliveryTimeFormat(),
				'deliveryTimeOff'			=> $this->oscConfig->getDeliveryTimeOff()
			]
		];
	}

	/**
	 * Address Fields
	 *
	 * @return array
	 */
	private function getAddressFields()
	{
		$fieldPosition = $this->oscConfig->getAddressFieldPosition();

		$fields = array_keys($fieldPosition);
		if (!in_array('country_id', $fields)) {
			array_unshift($fields, 'country_id');
		}

		return $fields;
	}

	/**
	 * Returns array of payment methods
	 * @return array
	 */
	private function getPaymentMethods()
	{
		$paymentMethods = [];
		$quote          = $this->checkoutSession->getQuote();
		foreach ($this->paymentMethodManagement->getList($quote->getId()) as $paymentMethod) {
			$paymentMethods[] = [
				'code'  => $paymentMethod->getCode(),
				'title' => $paymentMethod->getTitle()
			];
		}

		return $paymentMethods;
	}

	/**
	 * Returns array of payment methods
	 * @return array
	 */
	private function getShippingMethods()
	{
		$methodLists = $this->shippingMethodManagement->getList($this->checkoutSession->getQuote()->getId());
		foreach ($methodLists as $key => $method) {
			$methodLists[$key] = $method->__toArray();
		}

		return $methodLists;
	}

	/**
	 * @return bool
	 */
	private function isDisplaySocialLogin(){

		return $this->moduleManager->isOutputEnabled('Mageplaza_SocialLogin') && !$this->oscConfig->isDisabledSocialLoginOnCheckout();
	}
}
