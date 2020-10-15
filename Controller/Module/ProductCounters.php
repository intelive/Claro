<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Controller\Module;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Intelive\Claro\Helper\Data;
use Intelive\Claro\Model\Types\ProductCountersFactory;

class ProductCounters extends \Intelive\Claro\Controller\Module
{
    protected $resultJsonFactory;
    protected $productCountersFactory;

    /**
     * ProductCounters constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductCountersFactory $productCountersFactory
     * @param \Intelive\Claro\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductCountersFactory $productCountersFactory,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productCountersFactory = $productCountersFactory;
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

        $productCounters = $this->productCountersFactory->create();
        $data = $productCounters->load();
        $encodedData = $this->helper->prepareResult($data, 'productcounters', Data::TYPE_P_COUNTERS);

        return $result->setData($encodedData);
    }
}
