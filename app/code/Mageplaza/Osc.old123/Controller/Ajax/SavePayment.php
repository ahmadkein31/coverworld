<?php

namespace Mageplaza\Osc\Controller\Ajax;


class SavePayment extends AbstractCheckout
{
    /**
     * Save payment method action response
     *
     * @return json
     */
    public function execute()
    {
        $isSuccess = false;
        if ($this->_expireAjax()) return;
        $data = $this->getRequest()->getPost('payment');
        if (!$data) {
            $errMsg               = __('Please specify a payment method.');
            $result['messages'][] = $errMsg;
            $isSuccess            = true;
        } else {

            $saveResult = $this->getOnepage()->savePayment($data);
            if (isset($saveResult['error'])) {
                $result['messages'][] = $saveResult['message'];
                $isSuccess            = false;
            }

            try {
                $this->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            } catch (\Exception $e) {
                $errMsg            = __('Cannot set Payment Method.');
                $result['error'][] = $errMsg;
                $isSuccess         = false;
            }
        }

        $result['success'] = $isSuccess;
        if (!empty($result)) {
            $this->_result = $result;
        }

        return $this->getBodyResponse($this->_result);
    }
}
