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


namespace Mageplaza\Osc\Model\System\Config\Source;

class Enableddisabled
{
    const DISABLED_CODE = 0;
    const ENABLED_CODE  = 1;
    const DISABLED_LABEL = 'Disabled';
    const ENABLED_LABEL  = 'Enabled';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENABLED_CODE,
                'label' => __(self::ENABLED_LABEL),
            ],
            [
                'value' => self::DISABLED_CODE,
                'label' => __(self::DISABLED_LABEL),
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ENABLED_CODE  => __(self::ENABLED_LABEL),
            self::DISABLED_CODE => __(self::DISABLED_LABEL),
        ];
    }
}