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
namespace Mageplaza\Osc\Model\Plugin\Order;


/**
 * Class ItemConverter
 * @package Mageplaza\Osc\Model\Plugin\Cart\Totals
 */
class Item
{

    public function aroundGetProductOptions(
        \Magento\Sales\Model\Order\Item $subject,
        \Closure $proceed
    ){
       $data=$proceed();


        try {
         if(!is_string($data)){
            if(isset( $data['info_buyRequest']['fitment'])){
                $temp = array(
                    array(
                        'label'=>'Fits',
                        'value'=> $data['info_buyRequest']['fitment']
                    )
                );
            $result = array_merge($data['attributes_info'],$temp);
            $data['attributes_info']=$result;
            }
        }
        } catch (\Exception $e) {

         }
        return $data;
    }
}
