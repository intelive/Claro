<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

class Product
{
    const ENTITY_TYPE = 'product';

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
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $productAttributes
     * @return $this
     */
    public function parse($product, $productAttributes)
    {
        $this->entity_name = self::ENTITY_TYPE;
        $this->id = $product->getId();
        $this->created_at = $product->getCreatedAt();
        $this->sku = $product->getSku();
        $this->name = $product->getName();
        $this->type = $product->getTypeId();
        $this->created_at = $product->getCreatedAt();
        $this->updated_at = $product->getUpdatedAt();
        $this->price = $product->getData('price');
        $this->qty = $product->getQty();
        $this->visibility = $product->getVisibility() == 1 ? 0 : 1;
        $this->status = $product->getStatus() == 2 ? 0 : 1;

        if ($product->getTypeId() == 'simple') {
            $parentProductIds = $this->objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                ->getParentIdsByChild($product->getId());
            if (isset($parentProductIds[0])) {
                $this->parent_id = $parentProductIds[0];
            }
        }
        //build attrib list to take in
        $attributesList = ['name', 'sku', 'type_id', 'created_at', 'updated_at', 'visibility', 'status'];
        $options = new \StdClass;
        foreach ($productAttributes as $field => $usesSource) {
            $value = $product->getData($field);
            if (is_array($value) || is_object($value) || !in_array($field, $attributesList)) {
                continue;
            }

            $options->$field = $value;
        }

        $this->options = $options;

        return $this;
    }
}
