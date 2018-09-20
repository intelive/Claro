<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Model;

use Magento\Framework\Model\AbstractModel;

class ClaroReportsSync extends AbstractModel
{
    const CACHE_TAG = 'claroreports_sync';

    protected $_cacheTag = 'claroreports_sync';
    protected $_eventPrefix = 'claroreports_sync';

    protected function _construct()
    {
        $this->_init('Intelive\Claro\Model\ResourceModel\ClaroReportsSync');
    }
}