<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Block;

use Magento\Framework\View\Element\Template;

class Popup extends \Magento\Framework\View\Element\Template
{
    const POPUP_ONE_BY_ONE = 0;
    const POPUP_MULTIPLE = 1;

    /**
     * @var \Amasty\Promo\Helper\Config
     */
    private $config;

    public function __construct(Template\Context $context, \Amasty\Promo\Helper\Config $config, array $data = [])
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    public function getCountersMode()
    {
        return $this->config->getScopeValue("messages/display_remaining_gifts_counter");
    }
}
