<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Intelive\Claro\Helper\Data;

class EntityIds
{
    protected $objectManager;
    protected $helper;
    protected $resource;
    protected $connection;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Data $helper
    )
    {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->connection = $this->resource->getConnection();
    }

    public function load()
    {
        $result = new \stdClass();
        $result->product = $this->getProductIds();
        $result->customer = $this->getCustomerIds();
        $result->order = $this->getOrderIds();
        $result->invoice = $this->getInvoiceIds();
        $result->creditmemo = $this->getCreditmemoIds();
        $result->abandonedcart = $this->getAbandonedCartIds();

        return $result;
    }

    /**
     * @return mixed
     */
    protected function getAbandonedCartIds()
    {
        $abandonedTableName = $this->resource->getTableName('quote');
        $abandonedFrom = date('Y-m-d 00:00:00', strtotime('-14 days'));
        $abandonedTo = date('Y-m-d 23:59:59', strtotime('-1 day'));

        $sql = "SELECT MAX(`entity_id`) as max_id
                FROM {$abandonedTableName}
                WHERE `created_at` >= '$abandonedFrom' 
                AND `created_at` <= '$abandonedTo'
                AND `items_count` > '0' 
                AND `is_active` = '1'";
        $result = $this->connection->query($sql);

        return $result->fetchAll(\PDO::FETCH_OBJ)[0]->max_id;
    }

    /**
     * @return mixed
     */
    protected function getCreditmemoIds()
    {
        $creditmemoTableName = $this->resource->getTableName('sales_creditmemo');
        $sql = "SELECT MAX(`entity_id`) as max_id FROM {$creditmemoTableName}";

        $result = $this->connection->query($sql);
        return $result->fetchAll(\PDO::FETCH_OBJ)[0]->max_id;
    }

    /**
     * @return mixed
     */
    protected function getCustomerIds()
    {
        $customerTableName = $this->resource->getTableName('customer_entity');
        $sql = "SELECT MAX(`entity_id`) as max_id FROM {$customerTableName}";

        $result = $this->connection->query($sql);
        return $result->fetchAll(\PDO::FETCH_OBJ)[0]->max_id;
    }

    /**
     * @return mixed
     */
    protected function getInvoiceIds()
    {
        $invoiceTableName = $this->resource->getTableName('sales_invoice');
        $sql = "SELECT MAX(`entity_id`) as max_id FROM {$invoiceTableName}";

        $result = $this->connection->query($sql);
        return $result->fetchAll(\PDO::FETCH_OBJ)[0]->max_id;
    }

    /**
     * @return mixed
     */
    protected function getOrderIds()
    {
        $orderTableName = $this->resource->getTableName('sales_order');
        $sql = "SELECT MAX(`entity_id`) as max_id FROM {$orderTableName}";

        $result = $this->connection->query($sql);
        return $result->fetchAll(\PDO::FETCH_OBJ)[0]->max_id;
    }

    /**
     * @return mixed
     */
    protected function getProductIds()
    {
        $productTableName = $this->resource->getTableName('catalog_product_entity');
        $inventoryStockTableName = $this->resource->getTableName('cataloginventory_stock_item');

        $sql = "SELECT MAX(`e`.`entity_id`) as max_id FROM {$productTableName} AS `e`
                LEFT JOIN {$inventoryStockTableName} AS `at_qty` ON (at_qty.`product_id`=e.entity_id) AND (at_qty.stock_id=1)";

        $result = $this->connection->query($sql);
        return $result->fetchAll(\PDO::FETCH_OBJ)[0]->max_id;
    }
}