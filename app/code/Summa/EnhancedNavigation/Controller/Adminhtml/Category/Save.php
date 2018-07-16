<?php

/**
 * Class Save
 *
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

namespace Summa\EnhancedNavigation\Controller\Adminhtml\Category;

class Save
    extends \Magento\Catalog\Controller\Adminhtml\Category\Save
{
    /**
     * Filter category data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterCategoryPostData(array $rawData)
    {
        $data = parent::_filterCategoryPostData($rawData);
        // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
        if (isset($data['icon_image']) && is_array($data['icon_image'])) {
            if (!empty($data['icon_image']['delete'])) {
                $data['icon_image'] = null;
            } else {
                if (isset($data['icon_image'][0]['name']) && isset($data['icon_image'][0]['tmp_name'])) {
                    $data['icon_image'] = $data['icon_image'][0]['name'];
                } else {
                    unset($data['icon_image']);
                }
            }
        }
        return $data;
    }
}