<?php

namespace Prolutions\Catalog\Helper;

class Category extends \Magento\Catalog\Helper\Category
{
    public function getMaxChildLevel($category)
    {
        $max = $category->getLevel();
        $categories = $category->getChildrenCategories();
        foreach($categories as $childCategory){
            $newMax = $this->getMaxChildLevel($childCategory);
            if($newMax > $max){
                $max = $newMax;
            }
        }
        return $max;
    }

    public function getParentCategories($category)
    {
        $parentCategories = array();

        $category = $category->getParentCategory();
        while($category->getId() != $this->_storeManager->getStore()->getRootCategoryId()){
            $parentCategories[] = $category;
            $category = $category->getParentCategory();
        }

        return $parentCategories;
    }
}