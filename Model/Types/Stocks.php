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
    protected $stockCollectionFactory;
    protected $objectManager;
    protected $helper;

    public function __construct(
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\CollectionFactory $stockCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Data $helper
    ) {
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    public function load()
    {
        try {
            $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('cataloginventory_stock_item');

            $sql = "SELECT product_id as id, SUM(qty) as s FROM {$tableName} GROUP BY product_id HAVING s>0";

            $result = $connection->query($sql);

            $stock = new \stdClass();
            $stock->date = date('Y-m-d');
            $stock->stock = $result->fetchAll(\PDO::FETCH_ASSOC);

            $returnedIds = [];
            foreach ($stock->stock as $stock) {
                $returnedIds[] = $stock['id'];
            }

            return [
                'data' => $stock,
                'last_id' => $stock['id'],
                'returned_ids' => $returnedIds
            ];
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);

            return [];
        }
    }
}