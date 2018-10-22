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
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $productFactory;
    /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory  */
    protected $orderCollection;
    /** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory  */
    protected $invoicesFactory;
    /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory  */
    protected $shippingFactory;
    /** @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory  */
    protected $customerFactory;
    /** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory  */
    protected $creditmemosFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoicesFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shippingFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemosFactory,
        \Magento\Reports\Model\ResourceModel\Quote\Collection $cartCollection
    )
    {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->connection = $this->resource->getConnection();

        $this->productFactory = $productFactory;
        $this->orderCollection = $orderCollection;
        $this->invoicesFactory = $invoicesFactory;
        $this->shippingFactory = $shippingFactory;
        $this->customerFactory = $customerFactory;
        $this->creditmemosFactory = $creditmemosFactory;
        $this->cartCollection = $cartCollection;
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
        $filter = array(
            'datetime' => 1,
            'locale' => 'en_US',
            'from' => date('Y-m-d 00:00:00', strtotime('-14 days')),
            'to' => date('Y-m-d 23:59:59', strtotime('-1 day')),
        );

        $collection = $this->cartCollection
            ->addFieldToFilter('main_table.' . 'created_at', $filter);
        $collection->addFieldToFilter('main_table.items_count', ['gt' => 0]);
        $collection->addFieldToFilter('main_table.is_active', '1');
        $collection->setOrder('entity_id', 'DESC');
        $collection->setPageSize(1);

        return $collection->getLastItem()->getId();
    }

    /**
     * @return mixed
     */
    protected function getCreditmemoIds()
    {
        return $this->getEntityLastId($this->creditmemosFactory);
    }

    /**
     * @return mixed
     */
    protected function getCustomerIds()
    {
        return $this->getEntityLastId($this->customerFactory);
    }

    /**
     * @return mixed
     */
    protected function getInvoiceIds()
    {
        $config = $this->helper->getConfig();
        switch ($config['use_shipping']) {
            case Invoices::ORDER_W_INVOICE_DATA:
                $collection = $this->invoicesFactory->create();
                break;
            case Invoices::ORDER_W_SHIPPING_DATA:
                $collection = $this->shippingFactory->create();
                break;
            case Invoices::ORDER_W_ORDER_DATA:
                $collection = $this->orderFactory->create();
                break;
            default:
                $collection = $this->invoicesFactory->create();
                break;
        }

        $collection->setOrder('entity_id', 'DESC');
        $collection->setPageSize(1);

        return $collection->getLastItem()->getId();
    }

    /**
     * @return mixed
     */
    protected function getOrderIds()
    {
        return $this->getEntityLastId($this->orderCollection);
    }

    /**
     * @return mixed
     */
    protected function getProductIds()
    {
        return $this->getEntityLastId($this->productFactory);
    }

    /**
     * @param $entityFactory
     * @return mixed
     */
    protected function getEntityLastId($entityFactory)
    {
        $collection = $entityFactory->create()
            ->addAttributeToSelect('*');

        $collection->setOrder('entity_id', 'DESC');
        $collection->setPageSize(1);

        return $collection->getLastItem()->getId();
    }
}
