<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model;

use Magento\Framework\Model\AbstractModel;

class Feed extends \Magento\Framework\Model\AbstractModel
{
    protected $_export;
    protected $_rule;

    protected $_writer;

    protected $_exportConfig = array(
        'csv' =>'Amasty\Feed\Model\Export\Adapter\Csv',
        'txt' =>'Amasty\Feed\Model\Export\Adapter\Csv',
        'xml' =>'Amasty\Feed\Model\Export\Adapter\Xml'
    );

    protected $_objectManager;
    protected $_filesystem;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Feed\Model\Resource\Feed $resource = null,
        \Amasty\Feed\Model\Resource\Feed\Collection $resourceCollection = null,
        \Amasty\Feed\Model\Export\Product $export,
        \Amasty\Feed\Model\Rule $rule,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ){
        $this->_export = $export;
        $this->_rule = $rule;
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;


        parent::__construct(
                    $context,
                    $registry,
                    $resource,
                    $resourceCollection,
                    $data
                );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Feed\Model\Resource\Feed');
        $this->setIdFieldName('entity_id');
    }

    public function isCsv()
    {
        return $this->getFeedType() == 'txt' || $this->getFeedType() == 'csv';
    }

    public function isXml()
    {
        return $this->getFeedType() == 'xml';
    }

    public function getCsvField()
    {
        $ret = parent::getCsvField();

        if (!is_array($ret)){
            $config = unserialize($ret);
            $ret = array();

            if (is_array($config)){
                foreach($config as $item){
                    $ret[] = array(
                        'header' => isset($item['header']) ? $item['header'] : '',
                        'attribute' => isset($item['attribute']) ? $item['attribute'] : null,
                        'static_text' => isset($item['static_text']) ? $item['static_text'] : null,
                        'format' => isset($item['format']) ? $item['format'] : '',
                        'parent' => isset($item['parent']) ? $item['parent'] : '',
                        'modify' => isset($item['modify']) ? $item['modify'] : array(),
                    );

                }
            }
        }

        return $ret;
    }

    public function getFileFormat(){
        return $this->isCsv() ? 'csv' : $this->getFeedType();
    }

    protected function _getWriter()
    {
        if (!$this->_writer) {
            try {
                $this->_writer = $this->_objectManager->create(
                    $this->_exportConfig[$this->getFeedType()],
                    [
                        'destination' => $this->getFilename(),
                        'page' => $this->_export->getPage()
                    ]
                )->initBasics($this);

            } catch (\Exception $e) {
                $this->_logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the file format.'));
            }

            if (!$this->_writer instanceof \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'The adapter object must be an instance of %1.',
                        'Magento\ImportExport\Model\Export\Adapter\AbstractAdapter'
                    )
                );
            }
        }

        return $this->_writer;
    }

    public function getFilename(){
        $ret = parent::getFilename();
        $ext = '.' . $this->getFeedType();

        if (strpos($ret, $ext) === false){
            $ret .= $ext;
        }

        return $ret;
    }

    public function getContentType()
    {
        return $this->_getWriter()->getContentType();
    }

    protected function _getAttributes($parent = false){
        $attributes = array(
            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_PRODUCT_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_CATEGORY_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_CATEGORY_PATH_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_IMAGE_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE => array(),
            \Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE => array(),

        );

        if ($this->isCsv()){
            foreach($this->getCsvField() as $field){

                if (($parent && isset($field['parent']) && $field['parent'] == 'yes') ||
                    !$parent && isset($field['attribute'])) {
                    list($type, $code) = explode("|", $field['attribute']);

                    if (array_key_exists($type, $attributes))
                        $attributes[$type][$code] = $code;
                }


            }
        } else if ($this->isXml()) {
            $regex = "#{(.*?)}#";

            preg_match_all($regex, $this->getXmlContent(), $vars);

            if (isset($vars[1])) {

                foreach($vars[1] as $attributeRow){
                    preg_match("/attribute=\"(.*?)\"/", $attributeRow, $attrReg);
                    preg_match("/parent=\"(.*?)\"/", $attributeRow, $parentReg);

                    if (isset($attrReg[1])){
                        list($type, $code) = explode("|", $attrReg[1]);
                        $attributeParent = isset($parentReg[1]) ? $parentReg[1] : 'no';


                        if (($parent && $attributeParent == 'yes') ||
                            !$parent) {

                                if (array_key_exists($type, $attributes))
                                $attributes[$type][$code] = $code;
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    protected function getMatchingProductIds()
    {
        $this->_rule->setConditions([]);
        $this->_rule->setConditionsSerialized($this->getConditionsSerialized());
        $this->_rule->setStoreId($this->getStoreId());

        return array_keys($this->_rule->getFeedMatchingProductIds());
    }

    protected function getUtmParams()
    {
        $ret = array();

        if ($this->getUtmSource())
            $ret['utm_source'] = $this->getUtmSource();

        if ($this->getUtmMedium())
            $ret['utm_medium'] = $this->getUtmMedium();

        if ($this->getUtmTerm())
            $ret['utm_term'] = $this->getUtmTerm();

        if ($this->getUtmContent())
            $ret['utm_content'] = $this->getUtmContent();

        if ($this->getUtmCampaign())
            $ret['utm_campaign'] = $this->getUtmCampaign();
        return $ret;
    }

    public function getExport()
    {
        return $this->_export;
    }

    public function export($page)
    {
        $result = $this->_export
            ->setPage($page)
            ->setWriter($this->_getWriter())
            ->setAttributes($this->_getAttributes())
            ->setParentAttributes($this->_getAttributes(true))
            ->setMatchingProductIds($this->getMatchingProductIds())
            ->setUtmParams($this->getUtmParams())
            ->setStoreId($this->getStoreId())
            ->export();

        if ($this->getDeliveryEnabled() && $this->_export->getIsLastPage()){
            if ($this->getDeliveryType() == 'ftp'){
                $this->_ftpUpload();
            } else if ($this->getDeliveryType() == 'sftp'){
                $this->_sftpUpload();
            }
        }

        $this->setGeneratedAt(date('Y-m-d H:i:s'));
        $this->save();

        return $result;
    }

    public function getContents()
    {
        return $this->_getWriter()->getContents();
    }

    public function getMainPath()
    {
        return $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)->getAbsolutePath(). '/'. $this->getFilename();
    }

    protected function _getRemotePath(){
        $remotePath = $this->getDeliveryPath();
        if ('/' != substr($remotePath, -1, 1) && '\\' != substr($remotePath, -1, 1)) {
            $remotePath .= '/';
        }
        $remoteFileName = substr($this->getMainPath(), strrpos($this->getMainPath(), '/') + 1);
        $remotePath .= $remoteFileName;

        return $remotePath;
    }

    protected function _ftpUpload()
    {
        if (false !== strpos($this->getDeliveryHost(), ':')) {
            list($ftpHost, $ftpPort) = explode(':', $this->getDeliveryHost());
        } else {
            $ftpHost = $this->getDeliveryHost();
            $ftpPort = 21;
        }

        $ftp = @ftp_connect($ftpHost, $ftpPort, 10);
        if (!$ftp) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can not connect the FTP server %1:%2.', $ftpHost, $ftpPort));
        }

        $ftpLogin = @ftp_login($ftp, $this->getDeliveryUser(), $this->getDeliveryPassword());
        if (!$ftpLogin) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can not log in to the server with user `%1` and password `%2`.', $this->getDeliveryUser(), $this->getDeliveryPassword()));
        }

        if ($this->getDeliveryPassiveMode()) {
            ftp_pasv($ftp, true);
        }
        $remotePath = $this->_getRemotePath();

        $upload = ftp_put($ftp, $remotePath, $this->getMainPath(), FTP_BINARY);

        if (!$upload) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can not upload the file to the folder %1. Please check write permissions', $remotePath));
        }

        ftp_close($ftp);

        return $this;
    }

    protected function _sftpUpload(){
        $srcFile = $this->getMainPath();
        $dstFile = $this->_getRemotePath();

        if (false !== strpos($this->getDeliveryHost(), ':')) {
            list($ftpHost, $ftpPort) = explode(':', $this->getDeliveryHost());
        } else {
            $ftpHost = $this->getDeliveryHost();
            $ftpPort = 22;
        }

        $ch = curl_init();

        $fp = fopen($srcFile, 'r');

        curl_setopt($ch, CURLOPT_URL, 'sftp://'.$this->getDeliveryUser().':'.$this->getDeliveryPassword().'@'.$this->getDeliveryHost().$dstFile);

        curl_setopt($ch, CURLOPT_UPLOAD, 1);

        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);

        curl_setopt($ch, CURLOPT_INFILE, $fp);

        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($srcFile));

        curl_exec ($ch);

        $error_no = curl_errno($ch);


        if ($error_no != 0) {
            $error = (string)curl_error($ch);
            throw new \Magento\Framework\Exception\LocalizedException(__($error));
        }

        curl_close ($ch);

        return $this;
    }

    public function getTemplateOptionHash()
    {
        $ret = array();

        foreach($this->getResourceCollection()
                    ->addFieldToFilter('is_template', 1) as $template){

            $ret[$template->getId()] = $template->getName();
        }

        return $ret;
    }
}