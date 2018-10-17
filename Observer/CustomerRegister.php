<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Observer;

use Intelive\Claro\Helper\Data;
use Magento\Customer\Model\Customer;

class CustomerRegister implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Intelive\Claro\Helper\Utmz */
    protected $utmzHelper;
    /** @var \Intelive\Claro\Model\ClaroReportsCampaignsFactory */
    protected $campaignsFactory;
    /** @var \Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns */
    protected $campaignsResourceModel;
    /** @var \Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns\Collection  */
    protected $campaignsResourceModelCollection;

    /** @var Data  */
    protected $helper;

    public function __construct(
        \Intelive\Claro\Helper\Utmz $utmzHelper,
        \Intelive\Claro\Model\ClaroReportsCampaignsFactory $campaignsFactory,
        \Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns $campaignsResourceModel,
        \Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns\Collection $campaignsResourceModelCollection,
        Data $helper
    ) {
        $this->utmzHelper = $utmzHelper;
        $this->campaignsFactory = $campaignsFactory;
        $this->campaignsResourceModel = $campaignsResourceModel;
        $this->campaignsResourceModelCollection = $campaignsResourceModelCollection;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomer();

        try {
            if ($this->utmzHelper->utmz) {

                $existingCampaign = $this->campaignsResourceModelCollection
                    ->addFieldToFilter('entity_id', $customer->getId())
                    ->addFieldToFilter('type', 'customer');

                if (count($existingCampaign) >= 1) {
                    return;
                }
                $campaign = $this->campaignsFactory->create();
                $campaign
                    ->setData('entity_id', $customer->getId())
                    ->setData('type', 'customer')
                    ->setData('source', $this->utmzHelper->utmz_source)
                    ->setData('medium', $this->utmzHelper->utmz_medium)
                    ->setData('content', $this->utmzHelper->utmz_content)
                    ->setData('campaign', $this->utmzHelper->utmz_campaign)
                    ->setData('gclid', $this->utmzHelper->utmz_gclid);

                $this->campaignsResourceModel->save($campaign);
            }
        } catch (\Exception $exception) {
            $this->helper->log($exception->getMessage() . ' Trace ' . $exception->getTraceAsString(), Logger::CRITICAL);
        }
    }
}
