<?php

namespace Prolutions\Catalog\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getProductQualifications($product, $limit = 0)
    {
        $ratings = array();
        foreach($product->getData() as $attribute => $value){
            if(substr($attribute, 0, 7) == 'rating_' && is_string($value)){
                $qualification['code'] = $attribute;
                $qualification['value'] = substr($value, 1);

                $qualification['value'] = 100*$qualification['value']/5;

                $sortPosition = ord(substr($value, 0, 1));

                $ratings[$sortPosition] = $qualification;
            }
        }

        ksort($ratings);

        if($limit){
            $ratings = array_slice($ratings, 0, $limit);
        }

        return $ratings;
    }
}