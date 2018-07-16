<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer;

use Magento\Framework\View\Page\Config;
use MageWorx\SeoBase\Model\CanonicalFactory as CanonicalFactory;

/**
 * Observer class for canonical URL
 */
class Canonical implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageWorx\SeoBase\Model\CanonicalFactory
     */
    protected $canonicalFactory;

    /**
     * @var Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    public function __construct(
        Config    $pageConfig,
        CanonicalFactory $canonicalFactory
    ) {

        $this->pageConfig = $pageConfig;
        $this->canonicalFactory = $canonicalFactory;
    }

    /**
     * Set canonical URL to page config
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fullActionName = $observer->getFullActionName();
        $arguments      = ['layout' => $observer->getLayout(), 'fullActionName' => $fullActionName];
        $canonicalModel = $this->canonicalFactory->create($fullActionName, $arguments);
        $canonicalUrl   = $canonicalModel->getCanonicalUrl();

        if ($canonicalUrl) {
            $this->pageConfig->addRemotePageAsset(
                $canonicalUrl,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
    }
}
