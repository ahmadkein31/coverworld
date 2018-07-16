<?php

namespace Mageplaza\Osc\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;

class AddProduct extends Action
{
    /**
     * @var Cart
     */
    protected $_modelCart;

    /**
     * @var RedirectFactory
     */
    protected $_productCollection;

    public function __construct(
        Context $context,
        Cart $modelCart
    ) {
        parent::__construct($context);
        $this->_modelCart             = $modelCart;

    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $product_id     = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : 1;
        try {
            $cart    = $this->_modelCart;
            $product = ObjectManager::getInstance()->create('Magento\Catalog\Model\Product')->load($product_id);
            $cart->addProduct($product, array());
            $cart->save();
        } catch (\Exception $e) {
        }

        return $resultRedirect->setPath('onestepcheckout');

    }
}
