<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Creditmemo;
use Monolog\Logger;

class Creditmemos
{
    public $creditmemos = [];
    private $pageNum;
    protected $helper;
    protected $creditmemosFactory;
    protected $objectManager;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemosFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemosFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helper = $helper;
        $this->creditmemosFactory = $creditmemosFactory;
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
                $collection = $this->creditmemosFactory->create()
                    ->addAttributeToFilter('entity_id', $id);
            } elseif ($startDate && $endDate) {
                $from = date('Y-m-d 00:00:00', strtotime($startDate));
                $to = date('Y-m-d 23:59:59', strtotime($endDate));
                $collection = $this->creditmemosFactory->create()
                    ->addAttributeToFilter($filterBy, array('from' => $from, 'to' => $to));
            } else {
                $collection = $this->creditmemosFactory->create();
            }
            if ($fromId) {
                $collection->addFieldToFilter('main_table.entity_id', ['gteq' => $fromId]);
            }

            $collection->setOrder('created_at', $sortDir);
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);
            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }

            $lastId = [];
            $returnedIds = [];
            /** @var Creditmemo $creditmemo */
            foreach ($collection as $creditmemo) {
                if ($creditmemo && $creditmemo->getId()) {
                    $model = $this->objectManager->create('\Intelive\Claro\Model\Types\Creditmemo')->parse($creditmemo);
                    if ($model) {
                        $returnedIds[] = $creditmemo->getId();
                        $this->creditmemos['creditnote_' . $creditmemo->getIncrementId()] = $model;
                        $lastId[] = $creditmemo->getId();
                    }
                }
            }

            return [
                'data' => $this->creditmemos,
                'last_id' => !empty($lastId) ? max($lastId) : 0,
                'returned_ids' => $returnedIds
            ];

        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);
            return [];
        }

    }
}