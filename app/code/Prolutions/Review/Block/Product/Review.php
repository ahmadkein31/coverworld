<?php

namespace Prolutions\Review\Block\Product;

class Review extends \Magento\Review\Block\Product\Review
{
    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $title = __('Customer Reviews');
        $this->setTitle($title);
    }
}