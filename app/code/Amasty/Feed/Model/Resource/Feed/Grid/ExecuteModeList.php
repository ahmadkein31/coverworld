<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model\Resource\Feed\Grid;

class ExecuteModeList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return array(
            'manual' => __('Manual'),
            'hourly' => __('Hourly'),
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
        );
    }
}