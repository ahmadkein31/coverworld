<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * http://mageplaza.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2016 Mageplaza (http://mageplaza.com/)
 * @license     http://mageplaza.com/license-agreement/
 */

/**
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @author      Mageplaza Developer
 */
namespace Mageplaza\Osc\Model;

use Magento\Framework\DataObject;

class Status extends DataObject
{
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return [
            self::STATUS_ENABLED  => __('Active'),
            self::STATUS_DISABLED => __('Inactive')
        ];
    }


    public function toOptionArray()
    {
        return self::getOptionHash();
    }


    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = [];
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }
}