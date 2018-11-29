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
use Intelive\Claro\Model\Types\EntityIdsFactory;

class EntityIds extends \Intelive\Claro\Controller\Module
{
    protected $resultJsonFactory;
    protected $entityIdsFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EntityIdsFactory $entityIdsFactory,
        Data $helper
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->entityIdsFactory = $entityIdsFactory;
        $this->helper = $helper;

        parent::__construct($context);
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

        $entityIds = $this->entityIdsFactory->create();

        $data = $entityIds->load();

        return $result->setData($data);
    }
}