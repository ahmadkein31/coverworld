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
 * Osc Total Point Spend (for creditmemo) Block
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @author      Mageplaza Developer
 */
namespace Mageplaza\Osc\Block\Totals\Creditmemo;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

class Giftwrap extends Template
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $creditmemo  = $totalsBlock->getCreditmemo();
        if ($creditmemo && $creditmemo->getMcGiftwrapAmount() > 0.01) {
            $totalsBlock->addTotal(new DataObject([
                'code'        => 'mc_giftwrap_label',
                'label'       => __('Gift wrap'),
                'value'       => $creditmemo->getMcGiftwrapAmount(),
                'is_formated' => false,
            ]), 'subtotal');
        }
    }
}
