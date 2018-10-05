<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Controller\Module;

use Intelive\Claro\Model\Types\Creditmemo;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Intelive\Claro\Model\Types\CreditmemosFactory;
use Intelive\Claro\Helper\Data;

class CreditMemos extends \Intelive\Claro\Controller\Module
{
    protected $resultJsonFactory;
    protected $creditmemosFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Intelive\Claro\Model\Types\CreditmemosFactory $creditmemosFactory
     * @param \Intelive\Claro\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CreditmemosFactory $creditmemosFactory,
        Data $helper
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->creditmemosFactory = $creditmemosFactory;
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
        $creditmemos = $this->creditmemosFactory->create();

        $data = $creditmemos->load(
            $this->pageSize,
            $this->pageNum,
            $this->startDate,
            $this->endDate,
            $this->sortDir,
            $this->filterField,
            $this->id,
            $this->fromId
        );

        $encodedData = $this->helper->prepareResult($data, Creditmemo::ENTITY_TYPE);

        return $result->setData($encodedData);
    }
}