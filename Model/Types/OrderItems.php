<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

class OrderItems
{
    public $orderItems = [];
    private $pageNum;
    protected $helper;
    protected $orderItemsFactory;
    protected $objectManager;
    protected $eavConfig;
    protected $resource;
    protected $productMetadata;

    /**
     * @param \Intelive\Claro\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemsFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Intelive\Claro\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->helper = $helper;
        $this->orderItemsFactory = $orderItemsFactory;
        $this->objectManager = $objectManager;
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param $pageSize
     * @param $pageNum
     * @param null $startDate
     * @param null $endDate
     * @param $sortDir
     * @param $filterBy
     * @param $id
     * @param $fromId
     * @return $this|array
     */
    public function load($pageSize, $pageNum, $startDate = null, $endDate = null, $sortDir, $filterBy, $id, $fromId)
    {
        try {
            $store = $this->helper->getStore();
            $edition = $this->productMetadata->getEdition();
            $this->pageNum = $pageNum;
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cost');
            if ($id) {
                $collection = $this->orderItemsFactory->create()
                    ->addAttributeToFilter('main_table.item_id', $id);
            } elseif ($startDate && $endDate) {
                $from = date('Y-m-d 00:00:00', strtotime($startDate));
                $to = date('Y-m-d 23:59:59', strtotime($endDate));
                $collection = $this->orderItemsFactory->create()
                    ->addAttributeToFilter($filterBy, array('from' => $from, 'to' => $to));
            } else {
                $collection = $this->orderItemsFactory->create();
            }
            if ($fromId) {
                $collection->addFieldToFilter('main_table.item_id', ['gteq' => $fromId]);
            }

            $collection->addAttributeToFilter('main_table.store_id', $this->helper->getStore()->getStoreId());
            $catProdEntDecTable = $this->resource->getTableName('catalog_product_entity_decimal');
            if ($edition === 'Community') {
                $collection->getSelect()->joinLeft(
                    array('cost' => $catProdEntDecTable),
                    "main_table.product_id = cost.entity_id AND cost.attribute_id = {$attribute->getId()} AND cost.store_id = {$store->getStoreId()}",
                    array('cost' => 'value')
                );
            } else {
                $collection->getSelect()->joinLeft(
                    array('cost' => $catProdEntDecTable),
                    "main_table.product_id = cost.row_id AND cost.attribute_id = {$attribute->getId()} AND cost.store_id = {$store->getStoreId()}",
                    array('cost' => 'value')
                );
            }
            $collection->setOrder('created_at', $sortDir);
            $collection->setCurPage($pageNum);
            $collection->setPageSize($pageSize);
            if ($collection->getLastPageNumber() < $pageNum) {
                return $this;
            }
            $categories = [];
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($collection as $orderItem) {
                if ($orderItem && $orderItem->getId()) {
                    if ($orderItem->getParentItemId()) {
                        /** @var \Magento\Catalog\Model\Product $product */
                        $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($orderItem->getProductId());

                        $categories = [];
                        foreach ($product->getCategoryIds() as $index => $categoryId) {
                            if ($index > 4) {
                                break;
                            }
                            /** @var \Magento\Catalog\Model\Category $category */
                            $category = $this->objectManager->get('Magento\Catalog\Model\Category')->load($categoryId);
                            $categories[$index]['id'] = $category->getId();
                            $categories[$index]['name'] = $category->getName();
                        }
                    }
                    $model = $this->objectManager->create('\Intelive\Claro\Model\Types\OrderItem')->parse($orderItem);
                    if ($model) {
                        $this->orderItems['order_item_' . $orderItem->getId()] = (object)array_merge((array)$model, ['categories' => $categories]);
                    }
                }
            }

            return $this->orderItems;
        } catch (\Magento\Framework\Exception\LocalizedException $localizedException) {
            return [];
        }
    }
}
