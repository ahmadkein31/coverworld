<?php

namespace Prolutions\Checkout\Controller\Cart;
class Index extends \Magento\Checkout\Controller\Cart\Index
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Your Shopping Cart'));
        return $resultPage;
    }
}