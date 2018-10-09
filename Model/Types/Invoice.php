<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

class Invoice
{

    const ENTITY_TYPE = 'sales_invoice';

    protected $helper;
    protected $objectManager;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function parse($invoice)
    {
        $attributes = $invoice->getData();
        $currency = $attributes['order_currency_code'];

        $this->entity_name = self::ENTITY_TYPE;
        $this->id = $invoice->getId();
        $this->store_id = $invoice->getStoreId();
        $this->increment_id = $invoice->getIncrementId();
        $this->order_id = $invoice->getOrderId();
        $this->grand_total = $attributes['grand_total'];
        $this->shipping_amount = $attributes['shipping_amount'];
        $this->shipping_tax_amount = $attributes['shipping_tax_amount'];
        $this->subtotal = $attributes['subtotal'];
        $this->discount_amount = $attributes['discount_amount'];
        $this->tax_amount = $attributes['tax_amount'];
        $this->currency_code = $currency;
        $this->created_at = $attributes['created_at'];

        $config = $this->helper->getConfig();
        $items = [];
        foreach ($invoice->getItemsCollection() as $invoiceItem) {
            $qty = $config['use_shipping'] == Invoices::ORDER_W_ORDER_DATA ? $invoiceItem->getQtyOrdered() : $invoiceItem->getQty();
            $items[] = [
                'id' => $invoiceItem->getProductId(),
                'qty' => $qty
            ];
        }


        $this->items = $items;

        return $this;
    }
}
