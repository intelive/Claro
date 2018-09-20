<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Observer;

use Magento\Customer\Model\Customer;

class CustomerRegister implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Intelive\Claro\Helper\Utmz */
    protected $utmzHelper;

    /** @var \Intelive\Claro\Model\ClaroReportsCampaignsFactory */
    protected $campaignsFactory;
    /** @var \Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns */
    protected $campaignsResourceModel;

    public function __construct(
        \Intelive\Claro\Helper\Utmz $utmzHelper,
        \Intelive\Claro\Model\ClaroReportsCampaignsFactory $campaignsFactory,
        \Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns $campaignsResourceModel
    ) {
        $this->utmzHelper = $utmzHelper;
        $this->campaignsFactory = $campaignsFactory;
        $this->campaignsResourceModel = $campaignsResourceModel;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomer();

        try {
            if ($this->utmzHelper->utmz) {
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
        }
    }
}
