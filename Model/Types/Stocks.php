<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Intelive\Claro\Helper\Data;

class Stocks
{
    const CATALOG_INVENTORY_STOCK_ITEM_TABLE = 'cataloginventory_stock_item';
    protected $objectManager;
    protected $helper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Data $helper
    )
    {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    public function load()
    {
        try {
            $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName(self::CATALOG_INVENTORY_STOCK_ITEM_TABLE);

            $sql = "SELECT product_id as id, SUM(qty) as s FROM {$tableName} GROUP BY product_id HAVING s>0";

            $result = $connection->query($sql);

            $stockObj = new \stdClass();
            $stockObj->date = date('Y-m-d');
            $stockObj->stock = $result->fetchAll(\PDO::FETCH_ASSOC);

            $returnedIds = [];
            foreach ($stockObj->stock as $stock) {
                $returnedIds[] = $stock['id'];
            }

            return [
                'data' => $stockObj,
                'last_id' => $stock['id'],
                'returned_ids' => $returnedIds
            ];
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);

            return [];
        }
    }
}