<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Controller\Module;

use Intelive\Claro\Model\Types\Order;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Intelive\Claro\Model\Types\OrdersFactory;
use Intelive\Claro\Helper\Data;

class Orders extends \Intelive\Claro\Controller\Module
{

    protected $resultJsonFactory;
    protected $ordersFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Intelive\Claro\Model\Types\OrdersFactory $ordersFactory
     * @param \Intelive\Claro\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OrdersFactory $ordersFactory,
        Data $helper
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->ordersFactory = $ordersFactory;
        $this->helper = $helper;
        parent::__construct($context);
        parent::initParams();

    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if ($this->isAuthorized() != true || $this->isEnabled() != true) {
            $content = $this->helper->prepareDefaultResult();
            $result->setHttpResponseCode($content['status']);
            $result->setData($content['data']);
            
            return $result;
        }
        $orders = $this->ordersFactory->create();

        $data = $orders->load(
            $this->pageSize,
            $this->pageNum,
            $this->startDate,
            $this->endDate,
            $this->sortDir,
            $this->filterField,
            $this->id,
            $this->fromId
        );

        $encodedData = $this->helper->prepareResult($data, Order::ENTITY_TYPE);

        return $result->setData($encodedData);
    }
}
