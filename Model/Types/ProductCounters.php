<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\Types;

use Intelive\Claro\Helper\Data;

class ProductCounters
{
    const REPORT_EVENT_TABLE = 'report_event';
    const REPORT_EVENT_TYPE_TABLE = 'report_event_types';
    protected $objectManager;
    protected $helper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Data $helper
    )
    {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    public function load()
    {
        try {
            $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $eventTableName = $resource->getTableName(self::REPORT_EVENT_TABLE);
            $eventTypeTableName = $resource->getTableName(self::REPORT_EVENT_TYPE_TABLE);

            $eventNames = '"' . implode('", "', $this->getEventNames()) . '"';
            $reportEventTypes = "
            SELECT COUNT(e.event_id) as viewed, t.`event_name`, e.`object_id` as `product_id` FROM {$eventTableName} e
            INNER JOIN {$eventTypeTableName} t ON e.event_type_id = t.event_type_id
            WHERE t.`event_name` IN ($eventNames) GROUP BY e.`event_type_id`, e.`object_id`
            ";

            $result = $connection->query($reportEventTypes);

            $productCounters = [];
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $counter) {

                $productCounters[] = [
                    'product_id' => $counter['product_id'],
                    'event_name' => $counter['event_name'],
                    'viewed' => $counter['viewed']
                ];
            }

            $counterObj = new \stdClass();
            $counterObj->date = date('Y-m-d  H:i:s');
            $counterObj->counters = $productCounters;

            return [
                'data' => $counterObj,
                'last_id' => 0
            ];

        } catch (\Exception $ex) {

            return [];
        }
    }

    protected function getEventNames()
    {
        return [
            'catalog_product_view',
            'checkout_cart_add_product',
            'wishlist_add_product'
        ];
    }
}