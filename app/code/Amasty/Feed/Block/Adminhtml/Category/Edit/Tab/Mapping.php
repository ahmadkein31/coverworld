<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Widget;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Adminhtml tier price item renderer
 */
class Mapping extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory implements RendererInterface
{
    protected $_template = 'category/mapping.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getCategoriesList($node = null)
    {
        $list = array();

        if ($this->getRoot()->hasChildren()) {
            foreach($this->getRoot()->getChildren() as $node){
                $this->_getChildCategories($list, $node);
            }
        }

        return $list;
    }

    protected function _getChildCategories(&$list, $node, $level = 0)
    {
        $list[] = array(
            'name' => $node->getName(),
            'id' => $node->getId(),
            'level' => $level
        );

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->_getChildCategories($list, $child, $level + 1);
            }
        }
    }
}