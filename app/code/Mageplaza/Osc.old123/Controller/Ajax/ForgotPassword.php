<?php

namespace Mageplaza\Osc\Controller\Ajax;

use Magento\Customer\Model\AccountManagement;

class ForgotPassword extends AbstractCheckout
{

    /**
     * Loss pass
     */
    public function execute()
    {
        if (!$this->isAjax()) {
            return;
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $email  = (string)$this->getRequest()->getPost('email');
        $result = $this->_processForgot($email);
        if (!empty($result)) {
            $this->_result = $result;
        }

        return $this->getBodyResponse($this->_result);
    }

    protected function _processForgot($email)
    {
        $result = array();
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->_customerSession->setForgottenEmail($email);
                $result['error']     = true;
                $result['message'][] = __('Please correct the email address.');
            }

            try {
                $this->_accountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
                $result['success']   = true;
                $result['message'][] = __(
                    'If there is an account associated with %1 you will receive an email with a link to reset your password.',
                    $this->_escaper->escapeHtml($email)
                );
            } catch (NoSuchEntityException $e) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (\Exception $exception) {
                $result['error']     = true;
                $result['message'][] = __('We\'re unable to send the password reset email.');
            }

        }

        return $result;
    }
}
