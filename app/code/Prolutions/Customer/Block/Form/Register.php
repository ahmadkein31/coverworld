<?php

namespace Prolutions\Customer\Block\Form;

class Register extends \Magento\Customer\Block\Form\Register
{
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Create an account'));
        return $this;
    }
}