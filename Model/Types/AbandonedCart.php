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

class AbandonedCart
{

    const ENTITY_TYPE = 'abcart';

    protected $helper;
    protected $group;

    /**
     * @param \Magento\Customer\Model\Group $group
     * @param \Intelive\Claro\Helper\Data $helper
     */
    public function __construct(
        Group $group,
        Data $helper
    ) {
        $this->group = $group;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\Quote $cart
     * @return $this
     */
    public function parse($cart)
    {
        $this->entity = self::ENTITY_TYPE;
        $this->id = $cart->getId();
        $this->store_id = $cart->getStoreId();
        $this->customer_id = $cart->getCustomerId() ? $cart->getCustomerId() : 0;
        $this->created_at = $cart->getCreatedAt();
        $this->items = $cart->getAllItems();

        return $this;
    }
}
