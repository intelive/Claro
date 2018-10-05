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
use Magento\Framework\App\ProductMetadataInterfaceFactory;
use Intelive\Claro\Helper\Data;

class Version extends \Intelive\Claro\Controller\Module {

    protected $resultJsonFactory;

    protected $productMetadataInterfaceFactory;

    protected $helper;

    /** @var \Intelive\Claro\Model\ResourceModel\ClaroReportsSync */
    protected $syncResourceModel;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\ProductMetadataInterfaceFactory $productMetadataInterfaceFactory
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Intelive\Claro\Model\ResourceModel\ClaroReportsSync $syncResourceModel
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductMetadataInterfaceFactory $productMetadataInterfaceFactory,
        Data $helper,
        \Intelive\Claro\Model\ResourceModel\ClaroReportsSync $syncResourceModel
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->productMetadataInterfaceFactory = $productMetadataInterfaceFactory;
        $this->helper = $helper;
        $this->syncResourceModel = $syncResourceModel;

        parent::__construct($context);
        parent::initParams();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // call with token respond with:
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $productMetadata = $this->productMetadataInterfaceFactory->create();

        $result = $this->resultJsonFactory->create();
        $data = new \stdClass();

        $data->lastSentIdOrder = (string) $this->syncResourceModel->getLastIdDate('order');
        $data->lastSentIdProduct = (string) $this->syncResourceModel->getLastIdDate('product');
        $data->lastSentIdInvoice = (string) $this->syncResourceModel->getLastIdDate('invoice');
        $data->lastSentIdCreditmemo = (string) $this->syncResourceModel->getLastIdDate('creditmemo');
        $data->lastSentIdAbandonedCart = (string) $this->syncResourceModel->getLastIdDate('abandonedcart');
        $data->lastSentIdCustomer = (string) $this->syncResourceModel->getLastIdDate('customer');
        $data->claroPluginVersion = (string) $this->helper->getVersion();
        $data->claroModuleEnabled = $this->helper->getConfig()['enabled']; // sync status
        $data->magentoVersion = (string) $productMetadata->getVersion();
        $data->magentoEdition = (string) $productMetadata->getEdition();
        $data->phpVersion = (string) phpversion();
        $data->apiVersion = "2.0";
        $data->memoryLimit = @ini_get('memory_limit');
        $data->maxExecutionTime = @ini_get('max_execution_time');

        return $result->setData($data);
    }
}
