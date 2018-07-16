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
use Mageplaza\Osc\Helper\Checkout\Review\Giftmessage as MessageHelper;
use Mageplaza\Osc\Model\System\Config\Source\Giftmessage as SourceGiftmessage;
use Mageplaza\Osc\Block\Context;

class Giftmessage extends Review
{
    /**
     * @var messageHelper
     */
    protected $_messageHelper;

    public function __construct(
        Context $context,
        array $data = [],
        MessageHelper $messageHelper
    ) {
        parent::__construct($context, $data);

        $this->_messageHelper = $messageHelper;

    }

    /**
     * enable gift message or not
     *
     */
    public function canShow()
    {
        $reviewSection = SourceGiftmessage::REVIEW_SECTION;

        return $this->_messageHelper->isEnabled() == $reviewSection;
    }

    /**
     * @return string
     */
    public function getGiftMessageUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/saveGiftMessage', ['_secure' => $this->isSecure()]);
    }

    /**
     * @return GiftmessHelper|MessageHelper
     */
    public function getMessageHelper()
    {
        return $this->_messageHelper;
    }

}