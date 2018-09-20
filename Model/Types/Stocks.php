<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

class Stocks
{
    protected $stockCollectionFactory;
    protected $objectManager;

    public function __construct(
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\CollectionFactory $stockCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->objectManager = $objectManager;
    }

    public function load()
    {
        $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('cataloginventory_stock_item');

        $sql = "SELECT product_id as id, SUM(qty) as s FROM {$tableName} GROUP BY product_id HAVING s>0 limit 10";

        $result = $connection->query($sql);

        $stock = new \stdClass();
        $stock->date = date('Y-m-d');
        $stock->stock = $result->fetchAll(\PDO::FETCH_ASSOC);

        return $stock;
    }
}