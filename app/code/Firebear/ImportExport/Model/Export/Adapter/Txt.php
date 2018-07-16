<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Export\Adapter;

class Txt extends \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
{
    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * Source file handler.
     *
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_fileHandler;

    /**
     * Txt constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param null $destination
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $destination = null
    ) {
        register_shutdown_function([$this, 'destruct']);
        parent::__construct($filesystem, $destination);
    }

    /**
     * Object destructor.
     *
     * @return void
     */
    public function destruct()
    {
        if (is_object($this->_fileHandler)) {
            $this->_fileHandler->close();
        }
    }

    /**
     * Method called as last step of object instance creation. Can be overrided in child classes.
     *
     * @return $this
     */
    protected function _init()
    {
        $this->_fileHandler = $this->_directoryHandle->openFile($this->_destination, 'w');
        return $this;
    }

    /**
     * MIME-type for 'Content-Type' header.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/csv';
    }

    /**
     * Return file extension for downloading.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'txt';
    }

    /**
     * Set column names.
     *
     * @param array $headerColumns
     * @throws \Exception
     * @return $this
     */
    public function setHeaderCols(array $headerColumns)
    {
        if (null !== $this->_headerCols) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The header column names are already set.'));
        }
        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }

            $this->_fileHandler->write(implode($this->_delimiter, array_keys($this->_headerCols)) . "\n");
        }

        return $this;
    }

    /**
     * Write row data to source file.
     *
     * @param array $rowData
     * @throws \Exception
     * @return $this
     */
    public function writeRow(array $rowData)
    {
        if (null === $this->_headerCols) {
            $this->setHeaderCols(array_keys($rowData));
        }

        $writeData = $this->implodeData(
            array_merge($this->_headerCols, array_intersect_key($rowData, $this->_headerCols))
        );
        $this->_fileHandler->write(
            $writeData
        );

        return $this;
    }

    /**
     * @param $del
     */
    public function setDelimeter($del)
    {
        $this->_delimiter = $del;
    }

    /**
     * @param $enc
     */
    public function setEnclosure($enc)
    {
        $this->_enclosure = $enc;
    }

    protected function implodeData($data)
    {
        $str = "";
        $count = count($data);
        $inc = 0;
        foreach ($data as $key => $element) {
            $flArray = [];
            preg_match('/\n/i', $element, $flArray);
            if (strpos($element, $this->_delimiter) !== false) {
                $element = "\"" . $element . "\"";
            }
            if (count($flArray) > 0) {
                $newElement = str_replace("\"", "\\\"", str_replace(["\r", "\n"], '', $element));
                $str .= '"' . $newElement . '"';
            } else {
                $str .= $element;
            }

            if ($inc < $count) {
                $str .= $this->_delimiter;
            }
        }

        return $str . "\n";
    }
}
