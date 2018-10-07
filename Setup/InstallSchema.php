<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShopGo\SkyNet\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'skynet_service'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('skynet_service')
        )->addColumn(
            'service_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Service ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Service Name'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('skynet_service'),
                ['name'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'SkyNet Service Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
