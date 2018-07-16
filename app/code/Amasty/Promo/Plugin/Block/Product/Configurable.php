<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Block\Product;

class Configurable
{
    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
     */
    private $configurableAttributeData;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->helper = $helper;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->productMetadata = $productMetadata;
    }

    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        \Closure $proceed
    ) {
        if ($this->productMetadata->getVersion() < '2.2.0') {
            $store = $subject->getCurrentStore();
            $currentProduct = $subject->getProduct();

            $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
            $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

            $options = $this->helper->getOptions($currentProduct, $subject->getAllowProducts());
            $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

            $config = [
                'attributes' => $attributesData['attributes'],
                'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
                'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
                'optionPrices' => $this->getOptionPrices($subject),
                'priceFormat' => $this->localeFormat->getPriceFormat(),
                'prices' => [
                    'oldPrice' => [
                        'amount' => $this->localeFormat->getNumber($regularPrice->getAmount()->getValue()),
                    ],
                    'basePrice' => [
                        'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getBaseAmount()),
                    ],
                    'finalPrice' => [
                        'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getValue()),
                    ],
                ],
                'productId' => $currentProduct->getId(),
                'chooseText' => __('Choose an Option...'),
                'images' => isset($options['images']) ? $options['images'] : [],
                'index' => isset($options['index']) ? $options['index'] : [],
            ];

            if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
                $config['defaultValues'] = $attributesData['defaultValues'];
            }

            $config = array_merge($config, $this->_getAdditionalConfig());

            return $this->jsonEncoder->encode($config);
        }

        return $proceed();
    }

    /**
     * Returns additional values for js config, con be overridden by descendants
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        return [];
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @return array
     */
    protected function getOptionPrices(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject)
    {
        $prices = [];
        foreach ($subject->getAllowProducts() as $product) {
            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel =  $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                    'percentage' => $this->localeFormat->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    ),
                ];
            }

            $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                        ),
                    ],
                    'basePrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                        ),
                    ],
                    'finalPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getValue()
                        ),
                    ],
                    'tierPrices' => $tierPrices,
                ];
        }
        return $prices;
    }
}