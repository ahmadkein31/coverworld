<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model;

use MageWorx\SeoXTemplates\Helper\Data as HelperData;

abstract class Converter implements ConverterInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     *
     * @var Mage_Catalog_Model_Abstract|null
     */
    protected $_item = null;

    /**
     *
     * @param array $vars
     * @param string $templateCode
     * @return string
     */
    abstract protected function __convert($vars, $templateCode);

    /**
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * Retrive converted string from template code
     *
     * @param Mage_Catalog_Model_Abstract $item
     * @param string $templateCode
     * @return string
     */
    public function convert($item, $templateCode)
    {
        $this->_setItem($item);
        $vars = $this->parse($templateCode);
        $convertValue = $this->__convert($vars, $templateCode);

        return $convertValue;
    }

    /**
     *
     * @param \MageWorx\SeoXTemplates\Model\AbstractTemplate $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     *
     * @param Mage_Catalog_Model_Abstract $item
     */
    protected function _setItem($item)
    {
        $this->_item = $item;
    }

    /**
     * Retrive parsed vars from template code
     *
     * @param string $templateCode
     * @return array
     */
    public function parse($templateCode)
    {
        $vars = array();
        preg_match_all('~(\[(.*?)\])~', $templateCode, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            preg_match('~^((?:(.*?)\{(.*?)\}(.*)|[^{}]*))$~', $match[2], $params);
            array_shift($params);

            if (count($params) == 1) {
                $vars[$match[1]]['prefix']     = $vars[$match[1]]['suffix']     = '';
                $vars[$match[1]]['attributes'] = explode('|', $params[0]);
            }
            else {
                $vars[$match[1]]['prefix']     = $params[1];
                $vars[$match[1]]['suffix']     = $params[3];
                $vars[$match[1]]['attributes'] = explode('|', $params[2]);
            }
        }
        return $vars;
    }

}
