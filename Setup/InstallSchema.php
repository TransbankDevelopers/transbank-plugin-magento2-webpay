<?php
namespace Transbank\Webpay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Catalog module DB scheme
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createWebpayOrdersTable($setup, $context);
        $setup->endSetup();
    }
    
    protected function createWebpayOrdersTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $mainTable = $setup->getTable('webpay_orders_data');
        $table = $setup->getConnection()
            ->newTable($mainTable)
            ->addColumn('id', Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ], 'ID')
            ->addColumn('token', Table::TYPE_TEXT, 200, [
                'nullable' => false
            ], 'Token')
            ->addColumn('order_id', Table::TYPE_TEXT, 20, [
                'nullable' => false
            ], 'Order Id')
            ->addColumn('quote_id', Table::TYPE_TEXT, 20, [
                'nullable' => false
            ], 'Quote ID')
            ->addColumn('payment_status', Table::TYPE_TEXT, 30, [
                'nullable' => false
            ], 'Payment Status')
            // ->addColumn('grand_total', Table::TYPE_INTEGER, 11, [
            //         'nullable' => false
            //     ], 'grand_total')
            ->addColumn('metadata', Table::TYPE_TEXT, null, [
                'nullable' => false
            ], 'Metadata')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
            ], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
            ], 'updated_at')
            ->addIndex($setup->getTable('webpay_orders_data'), 'token');
        $setup->getConnection()->createTable($table);
    }
}
