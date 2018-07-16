<?php

namespace Mageplaza\Osc\Controller\Ajax;


class SaveForm extends AbstractCheckout
{
    public function execute()
    {
        if (!$this->isAjax()) {
            return;
        }
        $result = array();
        if ($this->getRequest()->isPost()) {
            $newData     = (array)$this->getRequest()->getPost();
            $currentData = is_array($this->getFormData()) ? $this->getFormData() : [];
            $this->setFormData(array_merge($currentData, $newData));
            $result['success'] = true;
        }

        return $this->getBodyResponse($result);
    }
}
