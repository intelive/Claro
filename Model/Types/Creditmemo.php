<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;


class Creditmemo
{
    const ENTITY_TYPE = 'sales_creditnote';

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
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function parse($creditmemo)
    {
        $attributes = $creditmemo->getData();
        $currency = $attributes['order_currency_code'];

        $this->entity_name = self::ENTITY_TYPE;
        $this->id = $creditmemo->getId();
        $this->store_id = $creditmemo->getStoreId();
        $this->increment_id = $creditmemo->getIncrementId();
        $this->order_id = $creditmemo->getOrder()->getId();
        $this->grand_total = $attributes['grand_total'];
        $this->shipping_amount = $attributes['shipping_amount'];
        $this->shipping_tax_amount = $attributes['shipping_tax_amount'];
        $this->subtotal = $attributes['subtotal'];
        $this->discount_amount = $attributes['discount_amount'];
        $this->tax_amount = $attributes['tax_amount'];
        $this->currency_code = $currency;
        $this->created_at = $attributes['created_at'];

        $items = [];
        foreach ($creditmemo->getItemsCollection() as $item) {
            $items[] = [
                'id' => $item->getProductId(),
                'qty' => $item->getQty()
            ];
        }

        $this->items = $items;

        return $this;
    }
}