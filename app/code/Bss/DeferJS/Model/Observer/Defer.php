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
namespace Bss\DeferJS\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Defer implements ObserverInterface
{
    public $helper;

    public function __construct(
        \Bss\DeferJS\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        $request = $observer->getEvent()->getRequest();
        if (!$this->helper->isEnabled($request)) {
            return;
        }

        $response = $observer->getEvent()->getResponse();
        if (!$response) {
            return;
        }

        $html = $response->getBody();
        if ($html == '') {
            return;
        }

        //get and remove script tag
        $conditionalJsPattern = '#(<\!--\[if[^\>]*>\s*<script.*</script>\s*<\!\[endif\]-->)|(<\!--\s*<script(?! nodefer).*</script>\s*-->)|(<script(?! nodefer).*</script>)#isU';
        preg_match_all($conditionalJsPattern, $html, $_matches);
        $_js = implode('', $_matches[0]);
        $html = preg_replace($conditionalJsPattern, '', $html);

        //defer iframe
        if ($this->helper->isDeferIframe()) {
            $conditionalJsPattern = '#<iframe([^>]*) src="([^"/]*/?[^".]*\.[^"]*)"([^>]*)>#';
            preg_match_all($conditionalJsPattern, $html, $_matches);
            $iframe = $_matches[0];
            if (!empty($iframe) > 0) {
                $replace = '<iframe$1 data-src="$2"$3>';
                $html = preg_replace($conditionalJsPattern, $replace, $html);
                $_js .= '<script>
                function init_defer(){
                    for(var t=document.getElementsByTagName("iframe"),e=0;e<t.length;e++)
                        t[e].getAttribute("data-src")&&t[e].setAttribute("src",t[e].getAttribute("data-src"));
                };
                window.onload=init_defer;</script>';
            }
        }

        //show path and controller in bottom page
        if ($this->helper->showControllersPath()) {
            $module = $request->getModuleName();
            $controller = $request->getControllerName();
            $action = $request->getActionName();

            $h = '<table cellspacing="0" cellpadding="2" border="1" style="width:auto;background-color:white">
                <tbody>
                    <tr>
                        <th>Controllers</th>
                        <th>Path</th>
                    </tr>
                    <tr>
                        <td align="left">'.$module.'_'.$controller.'_'.$action.'</td>
                        <td align="right">'.$request->getRequestUri().'</td>
                </tr>
                </tbody>
            </table>';
            $html .= $h;
        }

        if ($this->helper->inBody()) {
            //remove <body></html>
            $conditionalJsPattern = '#</body>\s*</html>#isU';
            preg_match_all($conditionalJsPattern, $html, $_matches);
            $_end = implode('', $_matches[0]);
            $html = preg_replace($conditionalJsPattern, '', $html);
            $html .= $_js.$_end;
        } else {
            $html .= $_js;
        }

        $response->setBody($html);
    }
}
