<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_DeferJS
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\DeferJS\Helper;

use \Magento\Store\Model\ScopeInterface;

class Data
{
    public $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($request)
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/active', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        //check home page
        $active =  $this->scopeConfig->getValue('deferjs/general/home_page', ScopeInterface::SCOPE_STORE);
        if ($active == 1 && $request->getFullActionName() == 'cms_index_index') {
            return false;
        }

        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        //check controller
        if ($this->regexMatchSimple(
            $this->scopeConfig->getValue('deferjs/general/controller', ScopeInterface::SCOPE_STORE),
            "{$module}_{$controller}_{$action}",
            1
        )
            ) {
            return false;
        }
        
        //check path
        if ($this->regexMatchSimple(
            $this->scopeConfig->getValue('deferjs/general/path', ScopeInterface::SCOPE_STORE),
            $request->getRequestUri(),
            2
        )
            ) {
            return false;
        }

        return true;
    }

    public function inBody()
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/in_body', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        return true;
    }

    public function isDeferIframe()
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/iframe', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        return true;
    }

    public function showControllersPath()
    {
        $active =  $this->scopeConfig->getValue('deferjs/general/show_path', ScopeInterface::SCOPE_STORE);
        if ($active != 1) {
            return false;
        }

        return true;
    }

    public function regexMatchSimple($regex, $matchTerm, $type)
    {

        if (!$regex) {
            return false;
        }

        $rules = @unserialize($regex);

        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            $regex = trim($rule['defer'], '#');
            if ($regex == '') {
                continue;
            }
            if ($type == 1) {
                $regexs = explode('_', $regex);
                switch (count($regexs)) {
                    case 1:
                    $regex = $regex.'_index_index';
                        break;
                    case 2:
                    $regex = $regex.'_index';
                        break;
                    default:
                        break;
                }
            }

            $regexp = '#' . $regex . '#';
            if (@preg_match($regexp, $matchTerm)) {
                return true;
            }
        }
        return false;
    }
}
