<?php

namespace Prolutions\Customer\Block\Form;

class Login extends \Magento\Customer\Block\Form\Login
{
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Login or create an account'));
        return $this;
    }
}