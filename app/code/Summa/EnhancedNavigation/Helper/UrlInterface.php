<?php

/**
 * Class UrlInterface
 */

namespace Summa\EnhancedNavigation\Helper;

interface UrlInterface
{
    /**
     * @param string $iconImage
     * @return string|null
     */
    public function getIconUrl($iconImage);
}