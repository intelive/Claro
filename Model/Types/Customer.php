<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Magento\Customer\Model\Group;
use Intelive\Claro\Helper\Data;

class Customer
{
    const ENTITY_TYPE = 'customer';
    const CHANNEL_UNTRACKED = '(untracked)';

    protected $helper;
    protected $addressFactory;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function parse($customer)
    {
        if (!$customer) {
            return $this;
        }

        $this->entity_name = self::ENTITY_TYPE;
        $this->id = $customer->getId();
        $this->store_id = $customer->getStoreId();
        $this->created_at = $customer->getCreatedAt();
        $this->dob = $customer->getDob();
        $this->name = $customer->getName();
        $this->gender = $customer->getGender();
        $this->group = $customer->getGroupId();
        if (is_object($customer->getDefaultBillingAddress())) {
            $this->bill_country =  $customer->getDefaultBillingAddress()->getCountryId();
        }
        if (is_object($customer->getDefaultShippingAddress())) {
            $this->ship_country = $customer->getDefaultShippingAddress()->getCountryId();
        }
        $this->source = $customer->getSource() ? $customer->getSource() : self::CHANNEL_UNTRACKED;
        $this->medium = $customer->getMedium() ? $customer->getMedium() : self::CHANNEL_UNTRACKED;
        $this->campaign = $customer->getContent() ? $customer->getContent() : self::CHANNEL_UNTRACKED;
        $this->content = $customer->getCampaign() ? $customer->getCampaign() : self::CHANNEL_UNTRACKED;
        $this->gclid = $customer->getGclid() ? $customer->getGclid() : self::CHANNEL_UNTRACKED;

        $this->email = $customer->getData('email');

        return $this;
    }
}
