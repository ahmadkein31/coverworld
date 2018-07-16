<?php

/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CommerceExtensions\CategoriesImportExport\Model\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *  CSV Import Handler
 */
class CsvImportHandler {

    protected $_categoryCache = array();

    /**
     * Resource instance
     *
     * @var Resource
     */
    protected $_resource;

    /**
     * Write connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Collection of publicly available stores
     *
     * @var \Magento\Store\Model\Resource\Store\Collection
     */
    protected $_publicStores;
    protected $_category;

    /**
     * Categoiry factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;
    protected $_logger;

    /**
     * @param \Magento\Store\Model\Resource\Store\Collection $storeCollection
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
    ResourceConnection $resource, \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection, \Magento\Catalog\Model\Category $category, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\File\Csv $csvProcessor
    ) {
        // prevent admin store from loading
        $this->_resource = $resource;
        $this->_publicStores = $storeCollection->setLoadDefault(true);
        $this->_category = $category;
        $this->_categoryFactory = $categoryFactory;
        $this->_logger = $logger;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields() {
        // indexes are specified for clarity, they are used during import
        return [
            0 => __('rootid'),
            1 => __('store'),
            2 => __('category_id'),
            3 => __('name'),
            4 => __('categories'),
            5 => __('level'),
            6 => __('position'),
            7 => __('description'),
            8 => __('url_key'),
            9 => __('is_active'),
            10 => __('display_mode'),
            11 => __('page_layout'),
            12 => __('cms_block'),
            13 => __('is_anchor'),
            14 => __('meta_title'),
            15 => __('meta_keywords'),
            16 => __('meta_description'),
            17 => __('category_image'),
            18 => __('include_in_menu'),
            19 => __('custom_layout_update'),
            20 => __('custom_design'),
            21 => __('custom_use_parent_settings'),
            22 => __('custom_apply_to_products')
        ];
    }

    public function getRowCategoryimport($option, $recordID, $entityid = 0) {
        //global $rec_id;
        // all code for opening connection
        // retrieving next record
        // $sql = "SELECT `rootid`, `store`, `category_id`, `name`, `categories`, `path`, `level`, `position`, `description`, `url_key`, `is_active`, `display_mode`, `page_layout`, `cms_block`, `is_anchor`, `meta_title`, `meta_keywords`, `meta_description`, `category_image`, `include_in_menu`, `custom_layout_update`, `custom_design`, `custom_use_parent_settings`, `custom_apply_to_products`, `category_products` FROM catalog_import WHERE is_processed=0;";
        // Need all fields except last one "is_processed"
        // return single row as ASSOCARRAY as it is processed by magento code as array

        switch ($option) {
            case "1":  // Get Record to process
                $query = "SELECT `rootid`, `store`, `name`, `categories`, `path`, `level`, `position`, `description`, `url_key`, `is_active`, `display_mode`, `page_layout`, `cms_block`, `is_anchor`, `meta_title`, `meta_keywords`, `meta_description`, `category_image`, `include_in_menu`, `custom_layout_update`, `custom_design`, `custom_use_parent_settings`, `custom_apply_to_products`, `category_products`, `rec_id` FROM catalog_import WHERE is_processed=0 LIMIT 1";
                break;
            case "2":
                // Not used currently!
                $query = "SELECT * FROM catalog_import WHERE is_processed = 1 LIMIT 1";
                break;
            case "3":
                $query = "UPDATE `catalog_import` SET is_processed=1 WHERE rec_id = " . $recordID;
                // update to 1 to indicate in Process of importing
                break;
            case "4":
                $query = "UPDATE `catalog_import` SET is_processed=2 WHERE rec_id = " . $recordID;
                // update to 2 to indicate it has been successfully processed
                break;
            case "5":
                // get entity_id from catalog_category_entity table
                $query = "SELECT entity_id FROM catalog_category_entity ORDER BY entity_id DESC LIMIT 1";
                // This query returns 1 record and that being the one with the highest entity_id (Last record imported)
                break;
            case "6":
                // get entity_id from catalog_category_entity table
                $query = "UPDATE `catalog_import` SET entity_id=" . $entityid . " WHERE rec_id = " . $recordID;
                // This query returns 1 record and that being the one with the highest entity_id (Last record imported)
                break;
            default:
                $query = "SELECT count(rec_id) FROM catalog_import WHERE is_processed=0";
        }

        $_connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        if ($option == "5") {
            $tblcatimport = $_connection->getTableName('catalog_category_entity');
        } else {
            $tblcatimport = $_connection->getTableName('catalog_import');
        }

        if ($option === "3" || $option === "4" || $option === "6") {
            $_connection->query($query);
            return;
        } else {
            $result1 = $_connection->fetchAll($query);
            return $result1;
        }
    }

    public function importFromImportTable($params) {
//die("testing code!");
        ini_set('error_reporting', E_ERROR);
        register_shutdown_function("fatal_handler");

        function fatal_handler() {
            $error = error_get_last();
            echo("<pre>");
            print_r($error);
        }

        if ($params['max_categories'] != "") {
            $max_categories = $params['max_categories'];
        } else {
            $max_categories = 20;
        }

        // microtime(true) returns the unix timestamp plus milliseconds as a float
        $starttime = microtime(true);

        $ratesData = $this->getRowCategoryimport($option, "0");
        #print_r($ratesData);
        $ttlnumrectogo = $ratesData[0]['count(rec_id)'];
        #echo($ttlnumrectogo);
        $cookiename = "catimportcontrol";
        $cookiename2 = "numrecordstoprocess";

        $numrecordstoprocess = $max_categories;
        if ($ttlnumrectogo === 0) {
            // delete cookie
            // add message to display this on the screen.
            // pause for keypress or mouseclick then continue
            setcookie($cookiename, '', time() - 3600, "/");
            //echo("processing complete");
            $numrecordstoprocess = 0; // skip loop to end
            exit();
        }

//        $option = "2";  // check to see if a record was partially processed and remove that record
//        $ratesData = $this->getRowCategoryimport($option, "");
//   leave this for the end in case we have any issues
        //die("test to here! again");

        for ($recnum = 1; $recnum <= $numrecordstoprocess; $recnum++) {

            //die("top of import loop");
            $option = "1";
            $ratesData = $this->getRowCategoryimport($option, "");
            $rec_id = $ratesData[0]['rec_id'];
            #echo("rec_id=" . $rec_id);

            if ($rec_id === 0 || empty($rec_id)) {
                #die('exited processing loop');
                break;
            }
            #die("data done test");
            $option = "3";
            $tempdata = $this->getRowCategoryimport($option, $rec_id);
            // loop through array

            foreach ($ratesData as $dataRow) {
                // Logic if name or url_key is NULL or empty then we must fix before checking for duplicate keys!
                #$path = "Boat Covers/Aquaforce/Fish Hawk 16'/2004";  // example
// DO NOT Modify Name or Path, that is supplied as is from the import file
                $data = $dataRow['categories'];
                $data = mb_ereg_replace("[\.\-\+\,\_\&\$\'\:\;\=\?\@\#\<\>\[\]\|\^\%\{\}]", " ", $data);
                // Note: I am presumming that we will never have more than 2 characters back to back from the above list.
                $data = str_replace(" /", "/", $data);
                $data = str_replace(" /", "/", $data);
                $data = str_replace("  ", " ", $data);
                $data = rtrim($data);
                //echo($dataRow['categories']. "<br>");
                $dataRow['categories'] = $data;
                //die($dataRow['categories']. "<br>");


                if (!empty($dataRow['name'])) {
                    $data = $dataRow['name'];
                    $data = mb_ereg_replace("[\.\-\+\,\_\&\$\'\:\;\=\?\@\#\<\>\[\]\|\^\%\{\}]", " ", $data);
                    // Note: I am presumming that we will never have more than 2 characters back to back from the above list.
                    $data = str_replace(" /", "/", $data);
                    $data = str_replace(" /", "/", $data);
                    $data = rtrim($data);
                    $dataRow['name'] = $data;
                }

//                print_r($dataRow);
                // unset rec_id from array
                foreach ($dataRow as $v) {
                    $k = key($v);
                    // Note: path not required for import! - Remove fields we don't want to use for import.
                    // When "Adding" categories, the Magento core code does NOT set the entity_id (category_id), rather it is only
                    // required if you are re-importing records and it is going to "update" an existing record.
                    // $k == "category_id" ||
                    // || $k == "path"
                    // Strip only rec_id column as it is not needed and can mess up insert!
                    if ($k == "rec_id" ) {
                        // unset this element from $arr array
                        unset($dataRow[$k]);
                    }
                }
                #echo("before import code<br>");
                $regionsCache = $this->_importRate($dataRow, $params);
                #echo("after import code<br>");
                // Mark as complete
                $option = "4";
                $tempdata = $this->getRowCategoryimport($option, $rec_id);

                //echo("before getting id of last record inserted<br>");
                // Get entity_id of highest record (the one last imported).
                $option = "5";
                $ratesData = $this->getRowCategoryimport($option, $rec_id);

                $entityid = $ratesData[0]['entity_id'];
                #echo($entityid . "<br>");
                // Update "entity_id" field in catalog_import table
                $option = "6";
                $tempdata = $this->getRowCategoryimport($option, $rec_id, $entityid);

//                echo($entityid . "<br>");
//                die("finished import to test entityid");
            }
        }
        // handle cookie stuff:  Note: maxcategories is reset to amount on form.
        #echo("ttlnumrectogo=" . $ttlnumrectogo . "<br>");
        #echo("recnum=" . $recnum . "<br>");
        //If we process just 1 record then we delete the cookie so the form does NOT resubmit
        setcookie($cookiename, "0 (Finished!)", time() - 3600, "/");
        if (($ttlnumrectogo - $recnum) <= 0 || $max_categories <= 1) {
            // delete cookie
            #echo("deleted cookie<br>");
            #setcookie($cookiename, '', time() - 12000, "/");
            #echo("No More Records to Process!");
            setcookie($cookiename2, '', time() - 3600, "/");
        } else {
            setcookie($cookiename, $max_categories, time() + 30, "/");
            $endtime = microtime(true);
            $timeelapsedseconds = round(($endtime - $starttime), 2);
            $ourmessage = ($ttlnumrectogo - $recnum) . " Last Batch of: (" . $max_categories . ") took: " . $timeelapsedseconds . " seconds.";
            setcookie($cookiename2, $ourmessage, time() + 30, "/");
        }
        //die("no error yet");
    }

    /**
     * Import Categories from CSV file
     * THIS FUNCTION IS NO LONGER BEING USED FOR IMPORTING CATEGORIES
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file, $params) {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        if ($params['import_delimiter'] != "") {
            $this->csvProcessor->setDelimiter($params['import_delimiter']);
        }
        if ($params['import_enclose'] != "") {
            $this->csvProcessor->setEnclosure($params['import_enclose']);
        }

        $RawData = $this->csvProcessor->getData($file['tmp_name']);
        // first row of file represents headers
        $fileFields = $RawData[0];
        #$validFields = $this->_filterFileFields($fileFields);
        #print_r($validFields);
        #$invalidFields = array_diff_key($fileFields, $validFields);
        #print_r($invalidFields);
        $ratesData = $this->_filterData($fileFields, $RawData);
        #print_r($ratesData);
        // store cache array is used to quickly retrieve store ID when handling locale-specific tax rate titles
        #$storesCache = $this->_composeStoreCache($validFields);
        #$regionsCache = [];
        foreach ($ratesData as $dataRow) {
            // skip headers
            #if ($rowIndex == 0) {
            #continue;
            #}
            $regionsCache = $this->_importRate($dataRow, $params);
        }
        #exit;
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields) {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            if ($this->_isStoreCodeValid($titleFieldName)) {
                // if store is still valid, append this field to valid file fields
                $filteredFields[$index] = $titleFieldName;
            }
        }

        return $filteredFields;
    }

    /**
     * Filter data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rateRawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _filterData(array $RawDataHeader, array $RawData) {
        $rowCount = 0;
        #$RawDataHeader = array();
        $RawDataRows = array();
        #print_r($RawData);
        #$validFieldsNum = count($validFields);
        foreach ($RawData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($RawData[$rowIndex]);
                continue;
            }
            #print_r($RawDataHeader);
            /* we take rows from [0] = > value to [website] = base */
            if ($rowIndex > 0) {
                foreach ($dataRow as $rowIndex => $dataRowNew) {
                    $RawDataRows[$rowCount][$RawDataHeader[$rowIndex]] = $dataRowNew;
                }
            }
            #$RawDatafix[$dataRow] = $dataRow;
            // unset invalid fields from data row
            #foreach ($dataRow as $fieldIndex => $fieldValue) {
            #if (isset($invalidFields[$fieldIndex])) {
            #unset($RawData[$rowIndex][$fieldIndex]);
            #}
            #}
            // check if number of fields in row match with number of valid fields
            #if (count($RawData[$rowIndex]) != $validFieldsNum) {
            # throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
            #}
            $rowCount++;
        }
        return $RawDataRows;
    }

    /**
     * Compose stores cache
     *
     * This cache is used to quickly retrieve store ID when handling locale-specific tax rate titles
     *
     * @param string[] $validFields list of valid CSV file fields
     * @return array
     */
    protected function _composeStoreCache($validFields) {
        $storesCache = [];
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $validFieldsNum = count($validFields);
        // title related fields located right after required fields
        for ($index = $requiredFieldsNum; $index < $validFieldsNum; $index++) {
            foreach ($this->_publicStores as $store) {
                $storeCode = $validFields[$index];
                if ($storeCode === $store->getCode()) {
                    $storesCache[$index] = $store->getId();
                }
            }
        }
        return $storesCache;
    }

    /**
     * Check if public store with specified code still exists
     *
     * @param string $storeCode
     * @return boolean
     */
    protected function _isStoreCodeValid($storeCode) {
        $isStoreCodeValid = false;
        foreach ($this->_publicStores as $store) {
            if ($storeCode === $store->getCode()) {
                $isStoreCodeValid = true;
                break;
            }
        }
        return $isStoreCodeValid;
    }

    /**
     * Import single category
     *
     * @param array $Data
     * @param array $regionsCache cache of regions of already used countries (is used to optimize performance)
     * @param array $storesCache cache of stores related to tax rate titles
     * @return array regions cache populated with regions related to country of imported tax rate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importRate(array $Data, array $params) {
        $modelData = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $finalpath = $Data['path'];
        //
        if (isset($Data['store'])) {
            if (empty($Data['store'])) {
                $store = $this->getStoreByCode("admin");
                $currentStoreId = $store->getId(); //$currentStoreId = "0";
            } else {
                $store = $this->getStoreByCode($Data['store']);
                $currentStoreId = $store->getId();
                #$currentStoreId = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($Data['store'])->getId();
            }
        } else {
            $store = $this->getStoreByCode($Data['store']);
            $currentStoreId = $store->getId();
            #$currentStoreId = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($Data['store'])->getId();
        }
        if (isset($Data['rootid']) && $Data['rootid'] != "") {
            $rootId = $Data['rootid'];
        } else {
            $rootId = 2;
        }
        if (!$rootId) {
            return array();
        }
        $rootPath = '1/' . $rootId;
//        if (empty($this->_categoryCache[$store->getId()])) {
//
//            $collection = $objectManager->create('Magento\Catalog\Model\Category')->getCollection()
//                    ->setStore($store)
//                    ->addAttributeToSelect('name');
//            $collection->getSelect()->where("path like '" . $rootPath . "/%'");
//
//            foreach ($collection as $cat) {
//
//                $pathArr = explode('/', $cat->getPath());
//                $namePath = '';
//                for ($i = 2, $l = sizeof($pathArr); $i < $l; $i++) {
//                    if ($pathArr[$i] != "") {
//                        $name = $collection->getItemById($pathArr[$i])->getName();
//                        $namePath .= (empty($namePath) ? '' : '/') . trim($name);
//                    }
//                }
//                $cat->setNamePath($namePath);
//            }
//
//            $cache = array();
//            foreach ($collection as $cat) {
//                $cache[strtolower($cat->getNamePath())] = $cat;
//                $cat->unsNamePath();
//            }
//            $this->_categoryCache[$store->getId()] = $cache;
//        }
//        $cache = & $this->_categoryCache[$store->getId()];
        //Remove this line if your using ^ vs / as delimiter for categories.. fix for cat names with / in them
        if ($params['categorydelimiter'] == "/") {
            $Data['categories'] = preg_replace('#\s*/\s*#', '/', trim($Data['categories']));
        }
        $path = $rootPath;
        #$this->_logger->log(100,"PATH START: " .$path);
        $namePath = '';
        $i = 1;
        if ($params['categorydelimiter'] != "") {
            $delimitertouse = $params['categorydelimiter'];
        } else {
            $delimitertouse = "/";
        }

        $file = "";
        if ($params['import_images_by_url'] == "true") {
            if ($Data['category_image'] != "") {
                $_filesystem = $objectManager->create('Magento\Framework\Filesystem');
                $orgfile = $Data['category_image'];
                $path_parts = pathinfo($orgfile);
                $file = $path_parts['basename'];
                $fullpath = $_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('catalog') . '/category/' . $file;
                try {
                    #$filewithspacesreplaced = str_replace(" ","%20", $orgfile); //fix for urls with spaces in the Url
                    $ch = curl_init($orgfile);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                    $rawdata = curl_exec($ch);
                    curl_close($ch);
                    if (file_exists($fullpath)) {
                        #$file = $path_parts['filename'].".".$path_parts['extension'];
                        $fullpath = $_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('catalog') . '/category/' . $file;
                        if (file_exists($fullpath)) {
                            unlink($fullpath);
                        }
                    }
                    $fp = fopen($fullpath, 'x');
                    fwrite($fp, $rawdata);
                    fclose($fp);
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
                }
            }
        }
        //lets check and if available use the image we downloaded via http vs path set in csv
        if ($file != "") {
            $_category_image = $file;
        } else {
            $_category_image = $Data['category_image'];
        }

        if (isset($Data['rootid'])) {
            $modelData['root_id'] = $Data['rootid'];
        }
        if (isset($Data['store'])) {
            $modelData['store'] = $Data['store'];
        }
        if (isset($Data['category_id'])) {
            $modelData['category_id'] = $Data['category_id'];
        }
        if (isset($Data['name'])) {
            $modelData['name'] = $Data['name'];
        }
        if (isset($Data['categories'])) {
            #$categories = $Data['categories'];
            $categories = explode($delimitertouse, $Data['categories']);
        }
        if (isset($Data['level'])) {
            $modelData['level'] = $Data['level'];
        }
        if (isset($Data['position'])) {
            $modelData['position'] = $Data['position'];
        }
        if (isset($Data['description'])) {
            $modelData['description'] = $Data['description'];
        }
        if (isset($Data['url_key'])) {
            $modelData['url_key'] = $Data['url_key'];
        }
        if (isset($Data['is_active'])) {
            $modelData['is_active'] = $Data['is_active'];
        }
        if (isset($Data['display_mode'])) {
            $modelData['display_mode'] = $Data['display_mode'];
        }
        if (isset($Data['page_layout'])) {
            $modelData['page_layout'] = $Data['page_layout'];
        }
        if (isset($Data['cms_block'])) {
            $modelData['landing_page'] = $Data['cms_block'];
        }
        if (isset($Data['is_anchor'])) {
            $modelData['is_anchor'] = $Data['is_anchor'];
        }
        if (isset($Data['meta_title'])) {
            $modelData['meta_title'] = $Data['meta_title'];
        }
        if (isset($Data['meta_keywords'])) {
            $modelData['meta_keywords'] = $Data['meta_keywords'];
        }
        if (isset($Data['meta_description'])) {
            $modelData['meta_description'] = $Data['meta_description'];
        }
        #if(isset($Data['image'])) {
        $modelData['image'] = $_category_image;
        #}
        if (isset($Data['include_in_menu'])) {
            $modelData['include_in_menu'] = $Data['include_in_menu'];
        }
        if (isset($Data['custom_layout_update'])) {
            $modelData['custom_layout_update'] = $Data['custom_layout_update'];
        }
        if (isset($Data['custom_design'])) {
            $modelData['custom_design'] = $Data['custom_design'];
        }
        if (isset($Data['custom_use_parent_settings'])) {
            $modelData['custom_use_parent_settings'] = $Data['custom_use_parent_settings'];
        }
        if (isset($Data['custom_apply_to_products'])) {
            $modelData['custom_apply_to_products'] = $Data['custom_apply_to_products'];
        }
        if (isset($Data['category_id'])) {
            $modelData['entity_id'] = $Data['category_id'];
        }
        #if(isset($Data['available_sort_by'])) {
        $modelData['available_sort_by'] = "name";
        #}
        #if(isset($Data['default_sort_by'])) {
        $modelData['default_sort_by'] = "name";
        #}
        $modelData['store_id'] = $currentStoreId;
        /*
          $modelData = [
          'root_id' => $Data['rootid'],
          'store' => $Data['store'],
          'category_id' => $Data['category_id'],
          'name' => $Data['name'],
          'categories' => $Data['categories'],
          'path' => $Data['path'],
          'level' => $Data['level'],
          'position' => $Data['position'],
          'description' => (isset($Data['description'])) ? $Data['description'] : '',
          'url_key' => (isset($Data['url_key'])) ? $Data['url_key'] : '',
          'is_active' => (isset($Data['is_active'])) ? $Data['is_active'] : '',
          'display_mode' => (isset($Data['display_mode'])) ? $Data['display_mode'] : '',
          'page_layout' => (isset($Data['page_layout'])) ? $Data['page_layout'] : '',
          'landing_page' => (isset($Data['cms_block'])) ? $Data['cms_block'] : '',
          'is_anchor' => (isset($Data['is_anchor'])) ? $Data['is_anchor'] : '',
          'meta_title' => (isset($Data['meta_title'])) ? $Data['meta_title'] : '',
          'meta_keywords' => (isset($Data['meta_keywords'])) ? $Data['meta_keywords'] : '',
          'meta_description' => (isset($Data['meta_description'])) ? $Data['meta_description'] : '',
          'image' => $_category_image,
          'include_in_menu' => (isset($Data['include_in_menu'])) ? $Data['include_in_menu'] : '',
          'custom_layout_update' => (isset($Data['custom_layout_update'])) ? $Data['custom_layout_update'] : '',
          'custom_design' => (isset($Data['custom_design'])) ? $Data['custom_design'] : '',
          'custom_use_parent_settings' => (isset($Data['custom_use_parent_settings'])) ? $Data['custom_use_parent_settings'] : '',
          'custom_apply_to_products' => (isset($Data['custom_apply_to_products'])) ? $Data['custom_apply_to_products'] : '',
          'entity_id' => $Data['category_id'],
          'available_sort_by' => 'name',
          'default_sort_by' => 'name',
          'store_id' => $currentStoreId,
          ];
         */

        // try to load existing category

        if (isset($Data['category_id'])) {
#die("wrong area - catid");
            $categoryModel = $this->_categoryFactory->create()->load($Data['category_id']);
            //$categoryModel = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($Data['category_id']);

            if ($categoryModel->getId()) {
                $categoryModel->addData($modelData);
                try {
                    $categoryModel->save();
                } catch (\Exception $e) {
                    #echo "ERROR: " . $e->getMessage();
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
                }
                #echo "UPDATE EXISTING";
                //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
                if (isset($Data['category_products']) && $Data['category_products'] != "") {
                    $connection = $this->_resource->getConnection();
                    $_catalog_category_product = $this->_resource->getTableName('catalog_category_product');

                    $pipedelimiteddatabycomma = explode(',', $Data['category_products']);
                    foreach ($pipedelimiteddatabycomma as $options_data) {

                        $option_parts = explode(':', $options_data);
                        $productId = $objectManager->create('Magento\Catalog\Model\Product')->getIdBySku($option_parts[0]);

                        if ($productId > 0) {
                            $connection->query("DELETE FROM " . $_catalog_category_product . " WHERE category_id = '" . $Data['category_id'] . "' AND product_id = '$productId'");
                            if (isset($option_parts[1])) {
                                $cat_product_position = $option_parts[1];
                            } else {
                                $cat_product_position = "0";
                            }

                            $connection->query("INSERT INTO " . $_catalog_category_product . " (category_id,product_id,position) VALUES ('" . $Data['category_id'] . "','$productId','$cat_product_position')");
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(
                            __('ERROR: PRODUCT DOES NOT EXIST')
                            );
                        }
                    }
                }
                //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
            } else {
                #echo "NEW CAT ID: " . $Data['category_id'] . "<br/>";
                if (isset($Data['category_id']) && $Data['category_id'] != "") {

                    $connection = $this->_resource->getConnection();
                    $_catalog_category_product = $this->_resource->getTableName('catalog_category_product');

                    #$this->_logger->log(100,"INSIDE START OF CAT W ID: " .$path);
//                    foreach ($categories as $catName) {
//                        $namePath .= (empty($namePath) ? '' : '/') . strtolower($catName);
//                        if (empty($cache[$namePath])) {
                            /// Get Root Category
                            #$rootCat = $this->_category->load($Data['rootid']);
                            /// Add a new sub category under root category
                            #$this->_logger->log(100,"BEING CAT: " .$path);
                            $categoryNew = $this->_categoryFactory->create();
                            $catAttributeSetID = $categoryNew->getDefaultAttributeSetId();
                            $categoryNew->setName($catName);
                            $categoryNew->setIsActive($Data['is_active']);
                            if (isset($Data['url_key'])) {
                                $categoryNew->setUrlKey($Data['url_key']);
                            }
                            if (isset($Data['description'])) {
                                $categoryNew->setData('description', $Data['description']);
                            }
                            if (isset($Data['is_anchor'])) {
                                $categoryNew->setData('is_anchor', $Data['is_anchor']);
                            }
                            if (isset($Data['display_mode'])) {
                                $categoryNew->setData('display_mode', $Data['display_mode']);
                            }
                            if (isset($Data['page_layout'])) {
                                $categoryNew->setData('page_layout', $Data['page_layout']);
                            }
                            if (isset($Data['cms_block'])) {
                                $categoryNew->setData('landing_page', $Data['cms_block']);
                            }
                            if (isset($Data['meta_title'])) {
                                $categoryNew->setData('meta_title', $Data['meta_title']);
                            }
                            if (isset($Data['meta_keywords'])) {
                                $categoryNew->setData('meta_keywords', $Data['meta_keywords']);
                            }
                            if (isset($Data['meta_description'])) {
                                $categoryNew->setData('meta_description', $Data['meta_description']);
                            }
                            if (isset($Data['include_in_menu'])) {
                                $categoryNew->setData('include_in_menu', $Data['include_in_menu']);
                            }
                            if (isset($Data['custom_layout_update'])) {
                                $categoryNew->setData('custom_layout_update', $Data['custom_layout_update']);
                            }
                            if (isset($Data['custom_design'])) {
                                $categoryNew->setData('custom_design', $Data['custom_design']);
                            }
                            if (isset($Data['custom_use_parent_settings'])) {
                                $categoryNew->setData('custom_use_parent_settings', $Data['custom_use_parent_settings']);
                            }
                            if (isset($Data['custom_apply_to_products'])) {
                                $categoryNew->setData('custom_apply_to_products', $Data['custom_apply_to_products']);
                            }
                            #$categoryNew->setId($Data['category_id']);
                            $categoryNew->setAttributeSetId($catAttributeSetID);
                            #$categoryNew->setParentId($rootNodeId);
                            $categoryNew->setStoreId($currentStoreId);
                            $categoryNew->setPath($Data['path']);
                            #$categoryNew->setPath($rootCat->getPath());
                            #$this->_logger->log(100,"BEFORE SAVE: " .$path);
                            $categoryNew->save();
                            $cache[$namePath] = $categoryNew;
//                        } else if (!empty($cache[$namePath])) {
//                            if ($Data['category_id'] != $cache[$namePath]->getId() && $catName == $Data['name']) {
//                                #$this->_logger->log(100,"MAKE NEW CAT: " .$Data['category_id']);
//                                #$this->_logger->log(100,"MAKE NEW NAME: " .$Data['name']);
//                                //THIS IS FOR WHEN WE ARE IMPORTING A NEW CATEGORY BUT THE NAME EXISTS ALREADY IN THE SAME FOLDER.. BUT WE WANT IT TWICE WITH SAME NAME
//                                /*
//                                  $categoryNew = $this->_categoryFactory->create();
//                                  $catAttributeSetID = $categoryNew->getDefaultAttributeSetId();
//                                  $categoryNew->setName($catName);
//                                  $categoryNew->setIsActive($Data['is_active']);
//                                  #if(isset($Data['url_key'])) { $categoryNew->setUrlKey($Data['url_key']); }
//                                  if(isset($Data['description'])) { $categoryNew->setData('description', $Data['description']); }
//                                  if(isset($Data['is_anchor'])) { $categoryNew->setData('is_anchor', $Data['is_anchor']); }
//                                  if(isset($Data['display_mode'])) { $categoryNew->setData('display_mode', $Data['display_mode']); }
//                                  if(isset($Data['page_layout'])) { $categoryNew->setData('page_layout', $Data['page_layout']); }
//                                  if(isset($Data['cms_block'])) { $categoryNew->setData('landing_page', $Data['cms_block']); }
//                                  if(isset($Data['meta_title'])) { $categoryNew->setData('meta_title', $Data['meta_title']); }
//                                  if(isset($Data['meta_keywords'])) { $categoryNew->setData('meta_keywords', $Data['meta_keywords']); }
//                                  if(isset($Data['meta_description'])) { $categoryNew->setData('meta_description', $Data['meta_description']); }
//                                  if(isset($Data['include_in_menu'])) { $categoryNew->setData('include_in_menu', $Data['include_in_menu']); }
//                                  if(isset($Data['custom_layout_update'])) { $categoryNew->setData('custom_layout_update', $Data['custom_layout_update']); }
//                                  if(isset($Data['custom_design'])) { $categoryNew->setData('custom_design', $Data['custom_design']); }
//                                  if(isset($Data['custom_use_parent_settings'])) { $categoryNew->setData('custom_use_parent_settings', $Data['custom_use_parent_settings']); }
//                                  if(isset($Data['custom_apply_to_products'])) { $categoryNew->setData('custom_apply_to_products', $Data['custom_apply_to_products']); }
//                                  $categoryNew->setAttributeSetId($catAttributeSetID);
//                                  $categoryNew->setStoreId($currentStoreId);
//                                  $categoryNew->save();
//                                  $cache[$namePath] = $categoryNew;
//                                 */
//                            }
//                        }
                        #$this->_logger->log(100,"NAME PATH: " .$namePath);
                        $catId = $cache[$namePath]->getId();
                        $path .= '/' . $catId;
                        $i++;
//                    }
                    //this code clones and then forces existing category Ids
                    $newpath = "";
                    $new_id = $Data['category_id'];
                    $copy = clone $categoryModel;
                    $currentcatname = $categoryNew->getName();
                    #echo "HERE: " . $currentcatname;
                    #$path = $categoryNew->getPath(); #$path = $rootCat->getPath(). '/'.$oldid;
                    $oldid = $categoryNew->getId();
                    $oldlevelid = $categoryNew->getLevel();
                    if ($oldlevelid < 1) {
                        $oldlevelid = 1;
                    }
                    $oldchilderncount = $categoryNew->getChildrenCount() - 1;
                    $oldattributesetid = $categoryNew->getDefaultAttributeSetId();
                    if ($oldchilderncount == "") {
                        $pathArr = explode('/', $path);
                        $l = sizeof($pathArr);
                        $oldchilderncount = $l - 2;
                    }
                    if ($oldchilderncount < 0) {
                        $oldchilderncount = 0;
                    }
                    $categoryNew->delete();

                    $a = explode("/", $path);
                    $count = 1;
                    foreach ($a as $i => $k) {
                        if ($count != count($a)) {
                            $newpath .= $k . "/";
                            $count++;
                        }
                    }
                    $newpath .= $new_id;
                    $generalupdateidset['name'] = $currentcatname;
                    $generalupdateidset['is_active'] = $Data['is_active'];
                    if (isset($Data['url_key'])) {
                        $generalupdateidset['url_key'] = $Data['url_key'];
                    }
                    if (isset($Data['is_anchor'])) {
                        $generalupdateidset['is_anchor'] = $Data['is_anchor'];
                    }
                    if (isset($Data['cms_block'])) {
                        $generalupdateidset['landing_page'] = $Data['cms_block'];
                    }
                    if (isset($Data['display_mode'])) {
                        $generalupdateidset['display_mode'] = $Data['display_mode'];
                    }
                    if (isset($Data['meta_title'])) {
                        $generalupdateidset['meta_title'] = $Data['meta_title'];
                    }
                    if (isset($Data['meta_keywords'])) {
                        $generalupdateidset['meta_keywords'] = $Data['meta_keywords'];
                    }
                    if (isset($Data['meta_description'])) {
                        $generalupdateidset['meta_description'] = $Data['meta_description'];
                    }
                    if (isset($Data['page_layout'])) {
                        $generalupdateidset['page_layout'] = $Data['page_layout'];
                    }
                    if (isset($Data['custom_layout_update'])) {
                        $generalupdateidset['custom_layout_update'] = $Data['custom_layout_update'];
                    }
                    if (isset($Data['custom_design'])) {
                        $generalupdateidset['custom_design'] = $Data['custom_design'];
                    }
                    if (isset($Data['include_in_menu'])) {
                        $generalupdateidset['include_in_menu'] = $Data['include_in_menu'];
                    }
                    if (isset($Data['custom_apply_to_products'])) {
                        $generalupdateidset['custom_apply_to_products'] = $Data['custom_apply_to_products'];
                    }
                    if (isset($Data['custom_use_parent_settings'])) {
                        $generalupdateidset['custom_use_parent_settings'] = $Data['custom_use_parent_settings'];
                    }
                    if (isset($Data['position'])) {
                        if ($Data['position'] != "") {
                            $generalupdateidset['position'] = $Data['position'];
                            $position = $Data['position'];
                        } else {
                            $position = "1";
                        }
                    }
                    $path = "";
                    $copy->addData($generalupdateidset);
                    #$this->_logger->log(100,"NEW PATH: ". $newpath);
                    $copy->setId($new_id)->setPath($newpath)->setPosition($position)->setLevel($oldlevelid)->setAttributeSetId($oldattributesetid)->setChildrenCount($oldchilderncount)->save();

//                    $cache[$namePath] = $copy;

                    //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
                    if (isset($Data['category_products']) && $Data['category_products'] != "") {
                        $pipedelimiteddatabycomma = explode(',', $Data['category_products']);
                        foreach ($pipedelimiteddatabycomma as $options_data) {

                            $option_parts = explode(':', $options_data);
                            $productId = $objectManager->create('Magento\Catalog\Model\Product')->getIdBySku($option_parts[0]);

                            if ($productId > 0) {
                                $connection->query("DELETE FROM " . $_catalog_category_product . " WHERE category_id = '" . $new_id . "' AND product_id = '$productId'");
                                if (isset($option_parts[1])) {
                                    $cat_product_position = $option_parts[1];
                                } else {
                                    $cat_product_position = "0";
                                }

                                $connection->query("INSERT INTO " . $_catalog_category_product . " (category_id,product_id,position) VALUES ('" . $new_id . "','$productId','$cat_product_position')");
                            } else {
                                throw new \Magento\Framework\Exception\LocalizedException(
                                __('ERROR: PRODUCT DOES NOT EXIST')
                                );
                            }
                        }
                    }
                    //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
                }
            }
        } else { //checks if category_id is set in csv or not
//            foreach ($categories as $catName) {
//                $namePath .= (empty($namePath) ? '' : '/') . strtolower($catName);
//                if (empty($cache[$namePath])) {
                    // Get Root Category
//                    echo("in foreach loop<br>");
                    #$rootCat = $this->_category->load($Data['rootid']);
                    // Add a new sub category under root category
                    $categoryNew = $this->_categoryFactory->create();
// added to see if we can eliminate record lock timeout issue.
//sleep(5);

                    $catAttributeSetID = $categoryNew->getDefaultAttributeSetId();
                    //$categoryNew->setName($catName);
                    $categoryNew->setName($Data['name']);
                    $categoryNew->setIsActive($Data['is_active']);
                    
//echo("url_key=" . $Data['url_key']."<br>");
//echo("categories=" . $Data['categories']."<br>");
//echo("catAttributeSetID=" . $Data['$catAttributeSetID']."<br>");                    
//echo("currentStoreId=" . $currentStoreId."<br>");
//echo("path=" . $finalpath."<br>");
//echo("category_id=" . $Data['category_id'] . "<br>");
//die("test");
                    
                    if (isset($Data['url_key'])) {
                        $categoryNew->setUrlKey($Data['url_key']);
                    }
                    if (isset($Data['description'])) {
                        $categoryNew->setData('description', $Data['description']);
                    }
                    if (isset($Data['is_anchor'])) {
                        $categoryNew->setData('is_anchor', $Data['is_anchor']);
                    }
                    if (isset($Data['display_mode'])) {
                        $categoryNew->setData('display_mode', $Data['display_mode']);
                    }
                    if (isset($Data['page_layout'])) {
                        $categoryNew->setData('page_layout', $Data['page_layout']);
                    }
                    if (isset($Data['cms_block'])) {
                        $categoryNew->setData('landing_page', $Data['cms_block']);
                    }
                    if (isset($Data['image'])) {
                        $categoryNew->setData('image', $Data['image']);
                    }
                    if (isset($Data['meta_title'])) {
                        $categoryNew->setData('meta_title', $Data['meta_title']);
                    }
                    if (isset($Data['meta_keywords'])) {
                        $categoryNew->setData('meta_keywords', $Data['meta_keywords']);
                    }
                    if (isset($Data['meta_description'])) {
                        $categoryNew->setData('meta_description', $Data['meta_description']);
                    }
                    if (isset($Data['include_in_menu'])) {
                        $categoryNew->setData('include_in_menu', $Data['include_in_menu']);
                    }
                    if (isset($Data['custom_layout_update'])) {
                        $categoryNew->setData('custom_layout_update', $Data['custom_layout_update']);
                    }
                    if (isset($Data['custom_design'])) {
                        $categoryNew->setData('custom_design', $Data['custom_design']);
                    }
                    if (isset($Data['custom_use_parent_settings'])) {
                        $categoryNew->setData('custom_use_parent_settings', $Data['custom_use_parent_settings']);
                    }
                    if (isset($Data['custom_apply_to_products'])) {
                        $categoryNew->setData('custom_apply_to_products', $Data['custom_apply_to_products']);
                    }
                    $categoryNew->setId($Data['category_id']);
                    
                    
                    
                    $categoryNew->setAttributeSetId($catAttributeSetID);
                    #$categoryNew->setParentId($rootNodeId);
                    $categoryNew->setStoreId($currentStoreId);
//                    echo($Data["path"] . "<br>");
                    //$categoryNew->setPath($path);
                    $categoryNew->setPath($finalpath);
                    #$categoryNew->setPath($rootCat->getPath());
                    $categoryNew->save();
// added to see if we can eliminate record lock timeout issue.
//sleep(2);
                    //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
                    if (isset($Data['category_products']) && $Data['category_products'] != "") {
                        $pipedelimiteddatabycomma = explode(',', $Data['category_products']);
                        $connection = $this->_resource->getConnection();
                        foreach ($pipedelimiteddatabycomma as $options_data) {

                            $option_parts = explode(':', $options_data);
                            $productId = $objectManager->create('Magento\Catalog\Model\Product')->getIdBySku($option_parts[0]);

                            if ($productId > 0) {
                                $connection->query("DELETE FROM " . $_catalog_category_product . " WHERE category_id = '" . $categoryNew->getId() . "' AND product_id = '$productId'");
                                if (isset($option_parts[1])) {
                                    $cat_product_position = $option_parts[1];
                                } else {
                                    $cat_product_position = "0";
                                }

                                $connection->query("INSERT INTO " . $_catalog_category_product . " (category_id,product_id,position) VALUES ('" . $categoryNew->getId() . "','$productId','$cat_product_position')");
                            } else {
                                throw new \Magento\Framework\Exception\LocalizedException(
                                __('ERROR: PRODUCT DOES NOT EXIST')
                                );
                            }
                        }
                    }
                    //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
//                    $cache[$namePath] = $categoryNew;
//                }
//                $catId = $cache[$namePath]->getId();
//                $path .= '/' . $catId;
                $i++;
//            }
        }

        /* THIS IS FOR UPDATING CATEGORY DATA */
//        $catId = $cache[$namePath]->getId();
//        echo($catId . "<br>");
//        die("before 2nd hit");
        
        $categoryModel = $this->_categoryFactory->create()->load($catId);
        if ($categoryModel->getId() > 0) {
            $categoryModel->addData($modelData);
            if (isset($Data['url_key'])) {
                $categoryModel->setUrlKey($Data['url_key']);
            }

            try {
                $categoryModel->save();
// added to see if we can eliminate record lock timeout issue.
//sleep(5);
            } catch (\Exception $e) {
                #echo "ERROR: " . $e->getMessage();
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
            }
            #echo "UPDATE EXISTING";
            //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS            
            if (isset($Data['category_products']) && $Data['category_products'] != "") {               
                $connection = $this->_resource->getConnection();
                $_catalog_category_product = $this->_resource->getTableName('catalog_category_product');

                $pipedelimiteddatabycomma = explode(',', $Data['category_products']);
                foreach ($pipedelimiteddatabycomma as $options_data) {

                    $option_parts = explode(':', $options_data);
                    $productId = $objectManager->create('Magento\Catalog\Model\Product')->getIdBySku($option_parts[0]);

                    if ($productId > 0) {
                        $connection->query("DELETE FROM " . $_catalog_category_product . " WHERE category_id = '" . $Data['category_id'] . "' AND product_id = '$productId'");
                        if (isset($option_parts[1])) {
                            $cat_product_position = $option_parts[1];
                        } else {
                            $cat_product_position = "0";
                        }

                        $connection->query("INSERT INTO " . $_catalog_category_product . " (category_id,product_id,position) VALUES ('" . $Data['category_id'] . "','$productId','$cat_product_position')");
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                        __('ERROR: PRODUCT DOES NOT EXIST')
                        );
                    }
                }
            }
            //CUSTOM CODE CATEGORY PRODUCT'S / POSITIONS
        }
        
    }

    protected function getStoreByCode($storeCode) {
        foreach ($this->_publicStores as $store) {
            if ($storeCode === $store->getCode()) {
                return $store;
            }
        }
        return false;
    }

    /**
     * Add regions of the given country to regions cache
     *
     * @param string $countryCode
     * @param array $regionsCache
     * @return array
     */
    protected function _addCountryRegionsToCache($countryCode, array $regionsCache) {
        if (!isset($regionsCache[$countryCode])) {
            $regionsCache[$countryCode] = [];
            // add 'All Regions' to the list
            $regionsCache[$countryCode]['*'] = '*';
            $regionCollection = clone $this->_regionCollection;
            $regionCollection->addCountryFilter($countryCode);
            if ($regionCollection->getSize()) {
                foreach ($regionCollection as $region) {
                    $regionsCache[$countryCode][$region->getCode()] = $region->getRegionId();
                }
            }
        }
        return $regionsCache;
    }

}
