<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $reportsTableName = $installer->getTable('claroreports_campaigns');
        $syncTableName = $installer->getTable('claroreports_sync');
        try {
            // Create the claroreports_campaigns table
            if ($installer->getConnection()->isTableExists($reportsTableName) != true) {
                $reportsTable = $installer->getConnection()
                    ->newTable($reportsTableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ]
                    )
                    ->addColumn(
                        'entity_id',
                        Table::TYPE_INTEGER,
                        11,
                        ['nullable' => false],
                        'order/customer id'
                    )
                    ->addColumn(
                        'type',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => 'order'],
                        'entity_type order/customer'
                    )
                    ->addColumn(
                        'source',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Source'
                    )
                    ->addColumn(
                        'medium',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Medium'
                    )
                    ->addColumn(
                        'content',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Content'
                    )
                    ->addColumn(
                        'campaign',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Campaign'
                    )
                    ->addColumn(
                        'gclid',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'gclid'
                    )
                    ->setComment('Stores google analytics utmz data')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $installer->getConnection()->createTable($reportsTable);
            }

            // Create the claroreports_sync table
            if ($installer->getConnection()->isTableExists($syncTableName) != true) {
                $syncTable = $installer->getConnection()
                    ->newTable($syncTableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ]
                    )
                    ->addColumn(
                        'entity',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'order, customer, creditmemo, product etc'
                    )
                    ->addColumn(
                        'last_sent_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false],
                        'id of the latest entity that has been synced'
                    )
                    ->addColumn(
                        'last_sent_date',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'date when the latest entity has been synced'
                    )
                    ->setComment('Stores the last entity id that was synced')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $installer->getConnection()->createTable($syncTable);
            }

            $installer->endSetup();
        } catch
        (\Exception $e) {
        }

    }
}