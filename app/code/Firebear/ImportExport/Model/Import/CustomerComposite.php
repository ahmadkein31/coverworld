<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import;

use Magento\CustomerImportExport\Model\Import\CustomerComposite as MagentoCustomer;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class CustomerComposite extends MagentoCustomer
{
    use \Firebear\ImportExport\Traits\General;

    protected $specialFields = [
        'reward_update_notification',
        'reward_warning_notification'
    ];
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    protected $_debugMode;

    protected $duplicateFields = [\Magento\CustomerImportExport\Model\Import\Customer::COLUMN_EMAIL];

    protected $_customerEntity;
    
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory,
        \Magento\CustomerImportExport\Model\Import\CustomerFactory $customerFactory,
        \Magento\CustomerImportExport\Model\Import\AddressFactory $addressFactory,
        \Symfony\Component\Console\Output\ConsoleOutput $output,
        \Firebear\ImportExport\Helper\Data $helper,
        \Firebear\ImportExport\Model\Import\CustomerFactory $fireImportCustomer,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $dataFactory,
            $customerFactory,
            $addressFactory,
            $data
        );
        $this->output = $output;
        $this->_logger = $logger;
        $this->_debugMode = $helper->getDebugMode();
        $this->_customerEntity = $fireImportCustomer->create(['data' => $data]);
    }

    /**
     * @return array
     */
    public function getAllFields()
    {
        $options = array_merge($this->getValidColumnNames(), $this->_specialAttributes);
        $options = array_merge($options, $this->_permanentAttributes);
        $options = array_merge($options, $this->specialFields);

        return array_unique($options);
    }
    
    public function setLogger($logger)
    {
        $this->_logger = $logger;
        $this->_customerEntity->setLogger($logger);
    }
}
