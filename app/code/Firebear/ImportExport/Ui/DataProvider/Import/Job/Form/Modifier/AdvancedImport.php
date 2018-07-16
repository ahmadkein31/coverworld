<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Ui\DataProvider\Import\Job\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Firebear\ImportExport\Model\Source\Config;

/**
 * Data provider for advanced inventory form
 */
class AdvancedImport implements ModifierInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var \Firebear\ImportExport\Model\Source\Config
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * AdvancedImport constructor.
     * @param ArrayManager $arrayManager
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param Config $config
     * @param \Magento\Framework\File\Size $fileSize
     */
    public function __construct(
        ArrayManager $arrayManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        Config $config,
        \Magento\Framework\File\Size $fileSize
    ) {
        $this->arrayManager = $arrayManager;
        $this->config = $config;
        $this->backendUrl = $backendUrl;
        $this->fileSize = $fileSize;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->prepareMeta($meta);
    }

    /**
     * @return array
     */
    protected function addFieldSource()
    {
        $maxImageSize = $this->fileSize->getMaxFileSize();
        $childrenArray = [];
        $generalConfig = [
            'componentType' => 'field',
            'component' => 'Firebear_ImportExport/js/form/import-dep-file',
            'formElement' => 'input',
            'dataType' => 'text',
            'source' => 'import',
            'valueUpdate' => 'afterkeydown'
        ];
        $types = $this->config->get();

        foreach ($types as $typeName => $type) {
            $sortOrder = 20;
            foreach ($type['fields'] as $name => $values) {
                $localConfig = [
                    'label' => $values['label'],
                    'dataScope' => $name,
                    'sortOrder' => $sortOrder,
                    'valuesForOptions' => [
                        $typeName => $typeName
                    ]
                ];
                if (isset($values['componentType']) && ($values['componentType'])) {
                    $localConfig['componentType'] = $values['componentType'];
                }
                if (isset($values['component']) && ($values['component'])) {
                    $localConfig['component'] = $values['component'];
                }
                if (isset($values['template']) && ($values['template'])) {
                    $localConfig['template'] = $values['template'];
                }
                if (isset($values['required']) && $values['required'] == "true") {
                    $localConfig['validation'] = [
                        'required-entry' => true
                    ];
                }
                if (isset($values['validation'])) {
                    if (strpos($values['validation'], " ") !== false) {
                        $array = explode(" ", $values['validation']);
                    } else {
                        $array = [$values['validation']];
                    }
                    foreach ($array as $item) {
                        $localConfig['validation'][$item] = true;
                    }
                }
                if (isset($values['url']) && $values['url']) {
                    $localConfig['uploaderConfig'] = [
                        'url' => $this->backendUrl->getUrl($values['url'])
                    ];
                }
                if (isset($values['notice']) && $values['notice']) {
                    $localConfig['notice'] = __($values['notice']);
                }
                if ($values['componentType'] == 'fileUploader') {
                    $localConfig['maxFileSize'] = $maxImageSize;
                }
                $sortOrder += 10;

                $config = array_merge($generalConfig, $localConfig);

                $childrenArray[$typeName . "_" . $name] = [
                    'arguments' => [
                        'data' => [
                            'config' => $config
                        ],
                    ]
                ];
            }
        }

        return $childrenArray;
    }

    /**
     * @return void
     */
    private function prepareMeta()
    {
        $meta['source'] = ['children' => $this->addFieldSource()];

        return $meta;
    }
}
