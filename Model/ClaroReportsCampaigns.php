<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model;

use Magento\Framework\Model\AbstractModel;

class ClaroReportsCampaigns extends AbstractModel
{
    const CACHE_TAG = 'claroreports_campaigns';

    protected $_cacheTag = 'claroreports_campaigns';
    protected $_eventPrefix = 'claroreports_campaigns';

    protected function _construct()
    {
        $this->_init('Intelive\Claro\Model\ResourceModel\ClaroReportsCampaigns');
    }
}