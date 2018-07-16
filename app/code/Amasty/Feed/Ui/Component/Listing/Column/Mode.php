<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class PaymentMethod
 */
class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $options;
    protected $_executeModeList;

    public function __construct(\Amasty\Feed\Model\Resource\Feed\Grid\ExecuteModeList $executeModeList)
    {
        $this->_executeModeList = $executeModeList;
    }

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = array();
            foreach($this->_executeModeList->toOptionArray() as $value => $label){
                $this->options[] = array(
                    'value' => $value,
                    'label' => $label
                );
            }
        }

        return $this->options;
    }
}