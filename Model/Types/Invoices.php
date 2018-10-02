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
    public $invoices = [];
    private $pageNum;
    protected $helper;
    protected $invoicesFactory;
    protected $objectManager;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoicesFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoicesFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helper = $helper;
        $this->invoicesFactory = $invoicesFactory;
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
            $this->pageNum = $pageNum;
            if ($id) {
                $collection = $this->invoicesFactory->create()
                    ->addAttributeToFilter('entity_id', $id);
            } elseif ($startDate && $endDate) {
                $from = date('Y-m-d 00:00:00', strtotime($startDate));
                $to = date('Y-m-d 23:59:59', strtotime($endDate));
                $collection = $this->invoicesFactory->create()
                    ->addAttributeToFilter($filterBy, array('from' => $from, 'to' => $to));
            } else {
                $collection = $this->invoicesFactory->create();
            }
            if ($fromId) {
                $collection->addFieldToFilter('main_table.entity_id', ['gteq' => $fromId]);
            }

            $collection->addAttributeToFilter('store_id', $this->helper->getStore()->getStoreId());
            $collection->setOrder('created_at', $sortDir);
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);
            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }

            /** @var Invoice $invoice */
            foreach ($collection as $invoice) {
                if ($invoice && $invoice->getId()) {
                    $model = $this->objectManager->create('\Intelive\Claro\Model\Types\Invoice')->parse($invoice);
                    if ($model) {
                        $this->invoices['invoice_' . $invoice->getId()] = $model;
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);
            $this->invoices = null;
        }

        return [
            'data' => $this->invoices,
            'last_id' => isset($invoice) ? $invoice->getId() : 0
        ];
    }
}