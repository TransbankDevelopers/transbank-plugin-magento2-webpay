<?php

namespace Transbank\Webpay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Magento\Framework\Filesystem\Io\File
     */
    protected $_io;

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
    * @param Magento\Framework\Filesystem\Io\File  $io
    * @param Magento\Framework\App\Filesystem\DirectoryList  $directoryList
    */
    public function __construct(
        File $io,
        DirectoryList $directoryList
    ) {
        $this->_io = $io;
        $this->_directoryList = $directoryList;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create a directory
         *
         * @param string "directory path"
         * @param int "directory permission"
         * @return bool
         */
        $this->_io->mkdir($this->_directoryList->getPath('var').'/log/Transbank_webpay', 0777);
        $installer->endSetup();
    }
}
