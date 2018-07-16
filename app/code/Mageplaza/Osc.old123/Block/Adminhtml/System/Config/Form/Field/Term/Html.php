<?php


namespace Mageplaza\Osc\Block\Adminhtml\System\Config\Form\Field\Term;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\LayoutFactory;

class Html extends Field
{
    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;


    public function __construct(Context $context, 
        array $data = [], 
        Config $wysiwygConfig = null, 
        LayoutFactory $viewLayoutFactory = null)
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_viewLayoutFactory = $viewLayoutFactory;

        parent::__construct($context, $data);
    }


    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_wysiwygConfig->isEnabled()) {
            $this->_viewLayoutFactory->create()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _getPathToSetting()
    {
        return 'groups[terms_conditions][fields][term_html][value]';
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $config = [
            'name'     => $this->_getPathToSetting(),
            'html_id'  => $element->getHtmlId(),
            'label'    => 'Content',
            'title'    => 'Content',
            'style'    => 'height:20em;width:550px',
            'required' => true,
            'config'   => $this->_getWysiwygConfig()
        ];
        $element->addData($config);

        return $element->getElementHtml();
    }

    protected function _getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config->addData(['hidden' => true, 'enabled' => false]);
        $config = $this->_addVariablesButton($config);

        return $config;
    }


    private function _addVariablesButton($config)
    {
        $variablePlugin = null;
        $plugins               = $config->getData('plugins');
        foreach ($plugins as $key => $item) {
            if ($item['name'] === 'magentovariable') {
                $variablePlugin = [
                    'key'  => $key,
                    'data' => $item
                ];
                break;
            }
        }
        if (is_null($variablePlugin)) {
            return $config;
        }

        $options = $variablePlugin['data']['options'];

        $originalUrl = $options['url'];
        $newUrl      = Mage::getUrl('adminhtml/osc_system_config_ajax/getVariables');
        if ($this->_storeManager->getStore()->isCurrentlySecure()) {
            $newUrl = Mage::getUrl(
				'adminhtml/osc_system_config_ajax/getVariables',
                ['_secure' => true]
            );
        }
        $options['url']                = $newUrl;
        $options['onclick']['subject'] = str_replace($originalUrl, $newUrl, $options['onclick']['subject']);

        $variablePlugin['data']['options'] = $options;


        $plugins[$variablePlugin['key']] = $variablePlugin['data'];
        $config->setData('plugins', $plugins);

        return $config;
    }
}

