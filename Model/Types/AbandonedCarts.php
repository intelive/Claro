<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Intelive\Claro\Helper\Data;
use Magento\Reports\Model\ResourceModel\Quote\Collection;
use Intelive\Claro\Model\Types\AbandonedCartFactory;
use Monolog\Logger;

class AbandonedCarts
{
    public $carts = [];
    protected $helper;
    protected $cartCollection;
    protected $abandonedCartFactory;
    private $pageNum;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Reports\Model\ResourceModel\Quote\Collection $cartCollection
     * @param \Intelive\Claro\Model\Types\AbandonedCartFactory
     */
    public function __construct(
        Data $helper,
        Collection $cartCollection,
        AbandonedCartFactory $abandonedCartFactory
    ) {
        $this->helper = $helper;
        $this->cartCollection = $cartCollection;
        $this->abandonedCartFactory = $abandonedCartFactory;
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
                $collection = $this->cartCollection
                    ->addFieldToFilter('main_table.entity_id', $id);
            } elseif ($startDate && $endDate) {
                $filter = array(
                    'datetime' => 1,
                    'locale' => 'en_US',
                    'from' => date('Y-m-d 00:00:00', strtotime($startDate)),
                    'to' => date('Y-m-d 23:59:59', strtotime($endDate)),
                );

                $collection = $this->cartCollection
                    ->addFieldToFilter('main_table.' . $filterBy, $filter);
            } else {
                $filter = array(
                    'datetime' => 1,
                    'locale' => 'en_US',
                    'from' => date('Y-m-d 00:00:00', strtotime('-14 days')),
                    'to' => date('Y-m-d 23:59:59', strtotime('-1 day')),
                );

                $collection = $this->cartCollection
                    ->addFieldToFilter('main_table.' . $filterBy, $filter);
            }
            $collection->addFieldToFilter('main_table.items_count', ['gt' => 0]);
            $collection->addFieldToFilter('main_table.is_active', '1');
            // Return abandoned carts that begin with the specified id
            if ($fromId) {
                $collection = $this->cartCollection
                    ->addFieldToFilter('main_table.entity_id', ['gteq' => $fromId]);
            }
            $collection->setOrder('updated_at', 'ASC');
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);

            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }

            $lastId = [];
            $returnedIds = [];
            /** @var \Magento\Quote\Model\Quote $cart */
            foreach ($collection as $cart) {
                if ($cart) {
                    $abandonedCarts = $this->abandonedCartFactory->create();
                    $model = $abandonedCarts->parse($cart);
                    if ($model) {
                        $returnedIds[] = $cart->getId();
                        $this->carts['quote_' . $cart->getId()] = $model;
                        $lastId[] = $cart->getId();
                    }
                }
            }

            return [
                'data' => $this->carts,
                'last_id' => !empty($lastId) ? max($lastId) : 0,
                'returned_ids' => $returnedIds
            ];
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);

            return [];
        }
    }
}
