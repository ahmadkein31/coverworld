<?php

namespace Mageplaza\Osc\Controller\Ajax;


class SaveGiftMessage extends AbstractCheckout
{
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $result      = array(
                'success' => true
            );
            $newData     = (array)$this->getRequest()->getPost();
            $currentData = is_array($this->getFormData()) ? $this->getFormData() : [];
            $this->setFormData(array_merge($currentData, $newData));
            $this->_eventManager->dispatch(
                'checkout_controller_onepage_save_shipping_method',
                [
                    'request' => $this->getRequest(),
                    'quote'   => $this->getOnepage()->getQuote()]);
        }
        if (!empty($result)) {
            $this->_result = $result;
        }

        return $this->getBodyResponse($this->_result);
    }
}
