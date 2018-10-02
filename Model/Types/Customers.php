<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Monolog\Logger;

class Customers
{
    public $customers = [];
    private $pageNum;
    protected $helper;
    protected $customerFactory;
    protected $mageCustomer;
    protected $objectManager;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param \Magento\Customer\Model\Customer $mageCustomer
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Model\Customer $mageCustomer,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->mageCustomer = $mageCustomer;
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
                $collection = $this->customerFactory->create()
                    ->addAttributeToFilter('entity_id', $id);
            } elseif ($startDate && $endDate) {
                $from = date('Y-m-d 00:00:00', strtotime($startDate));
                $to = date('Y-m-d 23:59:59', strtotime($endDate));
                $collection = $this->customerFactory->create()
                    ->addAttributeToFilter($filterBy, array('from' => $from, 'to' => $to));
            } else {
                $collection = $this->customerFactory->create();
            }
            $collection->addAttributeToFilter('store_id', $this->helper->getStore()->getStoreId());
            // Return customers that begin with the specified id
            if ($fromId) {
                $collection->addFieldToFilter('entity_id', ['gteq' => $fromId]);
            }

            $collection->setOrder('created_at', $sortDir);
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);
            $collection->getSelect()
                ->joinLeft(
                    array('campaigns' => 'claroreports_campaigns'), "e.entity_id=campaigns.entity_id AND campaigns.type='customer'", array('source', 'medium', 'content', 'campaign', 'gclid')
                )
                ->limit(1);

            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }
            /** @var \Magento\Customer\Model\Customer $customer */
            foreach ($collection as $customer) {
                if ($customer && $customer->getId()) {
                    $customerParser = $this->objectManager->create('\Intelive\Claro\Model\Types\Customer');
                    $model = $customerParser->parse($customer);
                    if ($model) {
                        $this->customers['customer_' . $customer->getId()] = $model;
                    }
                }
            }

        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);
            $this->customers = null;
        }

        return [
            'data' => $this->customers,
            'last_id' => isset($customer) ? $customer->getId() : 0
        ];
    }
}
