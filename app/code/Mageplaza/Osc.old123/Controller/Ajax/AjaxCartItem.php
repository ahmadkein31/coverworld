<?php

namespace Mageplaza\Osc\Controller\Ajax;


class AjaxCartItem extends AbstractCheckout
{
    /**
     *
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $action = $this->getRequest()->getParam('action');
        $id     = (int)$this->getRequest()->getParam('id');
        switch ($action) {
            case 'plus':
            case 'minus':
            case 'update':
                $this->_updateCartItem($action, $id);
                break;
            default:
                $this->_removeCartItem($id);
        }
    }


    /**
     * @param $action
     * @param $id
     */
    protected function _updateCartItem($action, $id)
    {
        $cart      = $this->getCart();
        $quoteItem = $cart->getQuote()->getItemById($id);
        $qty       = $quoteItem->getQty();
        $result    = [];
        if ($id) {
            try {
                if (isset($qty)) {
                    $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                        ['locale' => $this->_localeResolver->getLocale()]
                    );
                    $qty    = $filter->filter($qty);
                }
                if (!$quoteItem) {
                    $result['error']   = __('Quote item is not found.');
                    $result['success'] = false;
                }
                if ($action == 'update') {
                    $qty = $this->getRequest()->getParam('qty');
                } else if ($action == 'plus')
                    $qty++;
                else $qty--;
                try {
                    if ($qty == 0) {
                        $cart->removeItem($id);
                        $this->getCart()->save();
                    } else {
                        $quoteItem->setQty($qty);
                        $quoteItem->save();
                    }
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['error']   = $e->getMessage();
                }
                $message = $cart->getQuote()->getMessages();
                if ($message) {
                    $result['error']   = $message['qty']->getCode();
                    $result['success'] = false;
                    try {
                        $quoteItem->setQty($qty - 1)->save();
                        $this->getCart()->save();
                    } catch (Exception $e) {
                        $result['success'] = false;
                        $result['error']   = $e->getMessage();
                    }
                }
                if (!$quoteItem->getHasError()) {
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                }
            } catch (\Magento\Framework\Exception $e) {
                $result['success'] = false;
                $result['error']   = $e->getMessage();
            } catch (\Exception $e) {
                $result['success'] = false;
                $result['error']   = __('Can not save item.');
            }
            if (array_key_exists('error', $result)) {
                $result['success'] = false;

                return $this->getBodyResponse($result);
            } else {
                return $this->_updateOrderReview();
            }
        }
    }

    /**
     * @param $id
     */
    protected function _removeCartItem($id)
    {
        $result = [];
        if ($id) {
            try {
                $this->getCart()->removeItem($id)->save();
                $result['qty']     = $this->getCart()->getSummaryQty();
                $result['success'] = 1;
            } catch (\Exception $e) {
                $result['success'] = 0;
                $result['error']   = $e->getMessage();
            }
            if (array_key_exists('error', $result)) {
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->_resultRawFactory->create()->setContents($this->_helperAbstractHelper->jsonEncode($result));
            } else {
                $this->_updateOrderReview();
            }
        }
    }

}
