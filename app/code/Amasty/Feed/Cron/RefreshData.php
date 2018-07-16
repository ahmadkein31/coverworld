<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Cron;

use Magento\Framework\App\ResourceConnection;

class RefreshData
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate

    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
    }

    public function execute()
    {
//        set_time_limit(60 * 60);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $feed = $objectManager->create('\Amasty\Feed\Model\Feed');

        $collection = $feed->getResourceCollection()
            ->addFilter('is_active', 1);

        foreach($collection as $feed){
            try {
                if ($this->_onSchedule($feed)){

                    $page = 0;
                    while(!$feed->getExport()->getIsLastPage()){
                        $feed->export(++$page);
                    }
                }
            } catch (\Exception $e) {
                $objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
        }
    }

    protected function _validateTime($feed){
        $validate = true;
        $cronTime = $feed->getCronTime();

        if (!empty($cronTime)){
            $mageTime = $this->_localeDate->scopeTimeStamp();

            $validate = false;

            $times = explode(",", $cronTime);

            $now = (date("H", $mageTime) * 60) + date("i", $mageTime);

            foreach($times as $time){
                if ($now >= $time && $now < $time + 30){
                    $validate = true;
                    break;
                }
            }
        }
        return $validate;
    }

    protected function _onSchedule($feed)
    {
        $threshold = 24; // Daily
        switch ($feed->getExecuteMode()) {
            case 'weekly':
                $threshold = 168;
                break;
            case 'monthly':
                $threshold = 5040;
                break;
            case 'hourly':
                $threshold = 1;
                break;
        }
        if ($feed->getExecuteMode() != 'manual' && $threshold <= (strtotime('now') - strtotime($feed->getGeneratedAt()))/3600 &&
                $this->_validateTime($feed)) {
            return true;
        }
        return false;
    }
}