<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

class OrderItem
{
    protected $helper;
    protected $objectManager;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return $this
     */
    public function parse($orderItem)
    {
        $this->item_id = $orderItem->getId();
        $this->order_id = $orderItem->getOrderId();
        $this->sku = $orderItem->getSku();
        $this->name = $orderItem->getName();
        $this->qty = $orderItem->getQtyOrdered();
        $this->price = $orderItem->getPrice();
        $this->tax_amount = $orderItem->getTaxAmount();
        $this->parent_item_id = $orderItem->getParentItemId();
        $this->parent_item_sku = !is_null($orderItem->getParentItem()) ? $orderItem->getParentItem()->getSku() : null;
        $this->product_type = $orderItem->getProductType();
        $this->creation_date = $orderItem->getCreatedAt();
        $this->update_date = $orderItem->getUpdatedAt();

        $orderItemOptions = [];
        if (isset($orderItem->getProductOptions()['attributes_info'])) {
            foreach ($orderItem->getProductOptions()['attributes_info'] as $option) {
                $obj = new \stdClass();
                $obj->attribute_id = $option['option_id'];
                $obj->item_id = $orderItem->getId();
                $obj->label = $option['label'];
                $obj->value = $option['value'];
                $orderItemOptions[] = $obj;
            }
        }

        $this->options = $orderItemOptions;

        return $this;
    }
}
