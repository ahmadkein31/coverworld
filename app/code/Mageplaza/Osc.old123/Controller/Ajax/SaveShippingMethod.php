<?php

namespace Mageplaza\Osc\Controller\Ajax;

class SaveShippingMethod extends AbstractCheckout
{

    /**
     * Save shipping method action response
     *
     * @return json
     */
    public function execute()
    {

        if (!$this->getRequest()->isPost()) {
            $result['success']    = false;
            $result['messages'][] = __('Please select a shipping method.');

        } else {
            $data = $this->getRequest()->getPost('shipping_method', false);
            $this->_eventManager->dispatch(
                'checkout_controller_onepage_save_shipping_method',
                [
                    'request' => $this->getRequest(),
                    'quote'   => $this->getOnepage()->getQuote()]);
            $saveShippingResult = $this->getOnepage()->saveShippingMethod($data);
            if (!empty($saveShippingResult) && isset($saveShippingResult['error'])) {
                $result['success'] = false;
                if (isset($saveResult['message']))
                    $result['messages'][] = $saveShippingResult['message'];
            }
            $this->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
            $result['blocks'] = $this->getBlockHelper()->getActionBlocks();

        }
        if (!empty($result)) {
            $this->_result = $result;
        }

        return $this->getBodyResponse($this->_result);
    }

}
