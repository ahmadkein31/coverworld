<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block\Head;

abstract class Json extends \MageWorx\SeoMarkup\Block\Head
{
    /**
     * @return string (JSON-LD)
     */
    abstract protected function getMarkupHtml();

    /**
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        return $this->getMarkupHtml();
    }
}
