<?php

namespace Mageplaza\Osc\Controller\Ajax;

class AddGiftWrap extends AbstractCheckout
{

    /**
     *
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $is_used_giftwrap = $this->getRequest()->getParam('is_used_giftwrap', false);
        if ($is_used_giftwrap) {
            $this->_checkoutSession->setData('is_used_giftwrap', 1);
        } else {
            $this->_checkoutSession->setData('is_used_giftwrap', 0);
        }
        $this->_updateOrderReview();
    }
}
