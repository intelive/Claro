<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ClaroReportsSync extends AbstractDb
{
    protected $syncCollection;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        ClaroReportsSync\CollectionFactory $syncCollection
    )
    {
        $this->syncCollection = $syncCollection;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('claroreports_sync', 'id');
    }

    /**
     * Returns the last synced Id
     *
     * @param $entity
     * @return int
     */
    public function getLastId($entity) {
        $collection = $this->syncCollection
            ->create()
            ->addFieldToSelect(['*'])
            ->addFieldToFilter('entity', $entity)
            ->setOrder('last_sent_id', 'DESC')
            ->setPageSize('1')
            ->setCurPage('1')
        ;

        $lastId = 0;
        /** @var ClaroReportsSync $syncEntry */
        foreach ($collection as $syncEntry) {
            $lastId = $syncEntry->getLastSentId();
        }

        return $lastId;
    }

    /**
     * Returns the last synced Id along with the sync date
     * @param $entity
     * @return string
     */
    public function getLastIdDate($entity)
    {
        $collection = $this->syncCollection
            ->create()
            ->addFieldToSelect(['*'])
            ->addFieldToFilter('entity', $entity)
            ->setOrder('last_sent_id', 'DESC')
            ->setPageSize('1')
            ->setCurPage('1')
        ;

        $lastId = 0;
        $lastIdDate = 'Not defined';
        /** @var ClaroReportsSync $syncEntry */
        foreach ($collection as $syncEntry) {
            $lastId = $syncEntry->getLastSentId();
            $lastIdDate = $syncEntry->getLastSentDate();
        }

        return $lastId . ' (' . $lastIdDate . ')';
    }
}