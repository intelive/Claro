<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Invoice;
use Monolog\Logger;

class Invoices
{
    const ORDER_W_INVOICE_DATA = 1;
    const ORDER_W_SHIPPING_DATA = 2;
    const ORDER_W_ORDER_DATA = 3;

    public $invoices = [];
    private $pageNum;
    protected $helper;
    protected $invoicesFactory;
    protected $shippingFactory;
    protected $orderFactory;
    protected $objectManager;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoicesFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoicesFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shippingFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->helper = $helper;
        $this->invoicesFactory = $invoicesFactory;
        $this->shippingFactory = $shippingFactory;
        $this->orderFactory = $orderFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @param $pageSize
     * @param $pageNum
     * @param null $startDate
     * @param null $endDate
     * @param $sortDir
     * @param $filterBy
     * @param $id
     * @param $fromId
     * @return $this|array
     */
    public function load($pageSize, $pageNum, $startDate = null, $endDate = null, $sortDir, $filterBy, $id, $fromId)
    {
        try {
            $config = $this->helper->getConfig();
            switch ($config['use_shipping']) {
                case self::ORDER_W_INVOICE_DATA:
                    $collection = $this->invoicesFactory->create();
                    break;
                case self::ORDER_W_SHIPPING_DATA:
                    $collection = $this->shippingFactory->create();
                    break;
                case self::ORDER_W_ORDER_DATA:
                    $collection = $this->orderFactory->create();
                    break;
            }
            $this->pageNum = $pageNum;
            if ($id) {
                $collection->addAttributeToFilter('entity_id', $id);
            } elseif ($startDate && $endDate) {
                $from = date('Y-m-d 00:00:00', strtotime($startDate));
                $to = date('Y-m-d 23:59:59', strtotime($endDate));
                $collection->addAttributeToFilter($filterBy, array('from' => $from, 'to' => $to));
            }
            if ($fromId) {
                $collection->addFieldToFilter('main_table.entity_id', ['gt' => $fromId]);
            }

            $collection->setOrder('entity_id', $sortDir);
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);
            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }

            $lastId = [];
            $returnedIds = [];
            /** @var Invoice $invoice */
            foreach ($collection as $invoice) {
                if ($invoice && $invoice->getId()) {
                    $model = $this->objectManager->create('\Intelive\Claro\Model\Types\Invoice')->parse($invoice);
                    if ($model) {
                        $returnedIds[] = $invoice->getId();
                        $this->invoices['invoice_' . $invoice->getId()] = $model;
                        $lastId[] = $invoice->getId();
                    }
                }
            }

            return [
                'data' => $this->invoices,
                'last_id' => !empty($lastId) ? max($lastId) : 0,
                'returned_ids' => $returnedIds
            ];
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage() . ' Trace ' . $ex->getTraceAsString(), Logger::CRITICAL);

            return [];
        }
    }
}