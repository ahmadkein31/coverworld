<?php

namespace Mageplaza\Osc\Controller\Ajax;

class SaveCoupon extends AbstractCheckout
{

    /**
     * Apply Coupon Action Response
     *
     * @return json
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $code      = (string)$this->getRequest()->getParam('coupon_code');
        $oldCode   = $this->getQuote()->getCouponCode();
        $isApplied = false;
        $isSuccess = true;
        if (empty($code) && empty($oldCode) && $code !== $oldCode) {
            $isSuccess = false;
        } else {
            try {

                $this->getShippingAddress()->setCollectShippingRates(true);
                $this->getQuote()->setCouponCode(strlen($code) ? $code : '')
                    ->collectTotals()
                    ->save();

                if ($code == $this->getQuote()->getCouponCode()) {

                    $this->getShippingAddress()->setCollectShippingRates(true);
                    $this->getQuote()->setTotalsCollectedFlag(false);
                    $this->getQuote()->collectTotals()->save();
                    /**
                     * validate
                     */
                    if (empty($code)) {
                        $result['messages'][] = __('Coupon has been canceled.');
                        $isApplied            = false;

                    } else {
                        $result['messages'][] = __('Coupon has been applied.');
                        $isApplied            = true;

                    }

                } else {
                    $result['messages'][] = __('Coupon is invalid.');
                    $isSuccess            = false;
                }
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
                if ($this->_helperConfig->showGrandTotal()) {
                    $result['grand_total'] = $this->getGrandTotal();
                }
            } catch (Exception $e) {
                $result['messages'][] = $e->getMessage();
                $isSuccess            = false;
            } catch (\Exception $e) {
                $result['messages'][] = __('Error! cannot apply this coupon.');
                $isSuccess            = false;
            }
        }
        $result['success']        = $isSuccess;
        $result['coupon_applied'] = $isApplied;
        if (!empty($result)) {
            $this->_result = $result;
        }

        return $this->getBodyResponse($result);
    }
}
