<?php

namespace Mageplaza\Osc\Controller\Ajax;

class Login extends AbstractCheckout
{

    /**
     * customer login
     */
    public function execute()
    {
        $customerSession = $this->_customerSession;
        if (!$this->isAjax() || $customerSession->isLoggedIn()) {
            return;
        }
        $data = $this->getRequest()->getParam('login', array());
        if (!empty($data)) {
            $this->_result = $this->_processLogin($data, $customerSession);
        }

        return $this->getBodyResponse($this->_result);

    }

    protected function _processLogin($data)
    {
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        if ($username && $password) {
            try {
                $accountManage = $this->_accountManagement;
                $customer      = $accountManage->authenticate(
                    $username,
                    $password
                );
                $this->_customerSession->setCustomerDataAsLoggedIn($customer);
                $this->_customerSession->regenerateId();
            } catch (\Exception $e) {
                $result['error']   = true;
                $result['message'] = $e->getMessage();
            }
            if (!isset($result['error'])) {
                $result['success'] = true;
                $result['message'] = __('Login successfully. Please wait ...');
            }
        } else {
            $result['error']   = true;
            $result['message'] = __(
                'Please enter a username and password.');
        }

        return $result;
    }
}
