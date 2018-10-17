<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Magento\Framework\Exception\NoSuchEntityException;
use Monolog\Logger;

class Orders
{
    public $orders = [];
    protected $helper;
    protected $orderCollection;
    protected $objectManager;
    private $pageNum;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helper = $helper;
        $this->orderCollection = $orderCollection;
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
                $collection = $this->orderCollection->create()
                    ->addAttributeToFilter('main_table.increment_id', $id);
            } elseif ($startDate && $endDate) {
                $from = date('Y-m-d 00:00:00', strtotime($startDate));
                $to = date('Y-m-d 23:59:59', strtotime($endDate));
                $collection = $this->orderCollection->create()
                    ->addAttributeToFilter($filterBy, array('from' => $from, 'to' => $to));
            } else {
                $collection = $this->orderCollection->create();
            }
            if ($fromId) {
                $collection->addFieldToFilter('main_table.entity_id', ['gteq' => $fromId]);
            }

            $collection->addAttributeToSort('entity_id', $sortDir);
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);
            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }
            $campaignsTable = $collection->getResource()->getTable('claroreports_campaigns');

            $collection->getSelect()
                ->joinLeft(
                    array('campaigns' => $campaignsTable), "main_table.entity_id=campaigns.entity_id AND campaigns.type='order'", array('source', 'medium', 'content', 'campaign', 'gclid')
                )
                ->limit(1);

            $returnedIds = [];
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection as $order) {
                if ($order && $order->getId()) {
                    $model = $this->objectManager->create('\Intelive\Claro\Model\Types\Order')->parse($order);
                    if ($model) {
                        $returnedIds[] = $order->getId();
                        $this->orders['order_' . $order->getId()] = $model;
                    }
                }
            }

            return [
                'data' => $this->orders,
                'last_id' => isset($order) ? $order->getId() : 0,
                'returned_ids' => $returnedIds
            ];
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage() . ' Trace ' . $ex->getTraceAsString(), Logger::CRITICAL);
            return [];
        }

    }
}
