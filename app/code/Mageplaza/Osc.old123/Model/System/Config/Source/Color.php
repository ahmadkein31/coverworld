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
 * @copyright   Copyright (c) 2016 Mageplaza (http://mageplaza.com/)
 * @license     http://mageplaza.com/license-agreement.html
 */
namespace Mageplaza\Osc\Model\System\Config\Source;

class Color
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '#3399cc', 'label' => __('Default')],
            ['value' => 'orange', 'label' => __('Orange')],
            ['value' => 'green', 'label' => __('Green')],
            ['value' => 'black', 'label' => __('Black')],
            ['value' => 'blue', 'label' => __('Blue')],
            ['value' => 'darkblue', 'label' => __('Dark Blue')],
            ['value' => 'pink', 'label' => __('Pink')],
            ['value' => 'red', 'label' => __('Red')],
            ['value' => 'violet', 'label' => __('Violet')],
            ['value' => 'custom', 'label' => __('Custom')],
        ];
    }
}
