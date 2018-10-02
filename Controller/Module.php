<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Controller;

use Intelive\Claro\Helper\Data;

abstract class Module extends \Magento\Framework\App\Action\Action {

    protected $pageSize = null;
    protected $pageNum = 0;
    protected $startDate = null;
    protected $endDate = null;
    protected $sortDir = 'asc';
    protected $filterField = 'created_at';
    protected $id = null;
    protected $fromId = null;
    /** @var Data */
    protected $helper;

    protected function initParams() {
        if ((bool) $pageSize = $this->getRequest()->getParam('page_size')) {
            $this->pageSize = $pageSize;
        } else {
            $this->pageSize = '50';
        }
        if ((bool) $pageNum = $this->getRequest()->getParam('page_num')) {
            $this->pageNum = $pageNum;
        } else {
            $this->pageNum = '1';
        }
        if ((bool) $startDate = $this->getRequest()->getParam('start_date')) {
            $this->startDate = $startDate;
            if ((bool) $endDate = $this->getRequest()->getParam('end_date')) {
                $this->endDate = $endDate;
            } else {
                $this->endDate = date('Y-m-d');
            }
        } elseif ((bool) $updatedStartDate = $this->getRequest()->getParam('updated_start_date')) {
            $this->filterField = 'updated_at';
            $this->startDate = $updatedStartDate;
            if ((bool) $updatedEndDate = $this->getRequest()->getParam('updated_end_date')) {
                $this->endDate = $updatedEndDate;
            } else {
                $this->endDate = date('Y-m-d');
            }
        }
        if ((bool) $sortDir = $this->getRequest()->getParam('sort_dir')) {
            $this->sortDir = $sortDir;
        }
        if ((bool) $id = $this->getRequest()->getParam('id')) {
            $this->id = $id;
        }

        if ((bool) $fromId = $this->getRequest()->getParam('from_id')) {
            $this->fromId = $fromId;
        }

        $this->helper->log(
            "calledClass = " . get_class($this) . "; fromId = $this->fromId; pageSize = $this->pageSize; pageNum = $this->pageNum; startDate = $this->startDate; sortDir = $this->sortDir; id = $this->id"
        );
    }

    /**
     * @return mixed
     */
    protected function isEnabled() {
        return $this->helper->getConfig()['enabled'];
    }

    /**
     * @return bool
     */
    protected function isAuthorized() {
        $token = $this->helper->getConfig()['api_key'];
        $authToken = (isset($_SERVER['HTTP_X_CLARO_TOKEN']) ? $_SERVER['HTTP_X_CLARO_TOKEN'] : []);

        if (empty($authToken)) {
            return false;
        }

        if (trim($token) != trim($authToken)) {
            $this->helper->log('Claro feed request with invalid security token: '.$authToken.' compared to stored token: '.$token);
            return false;
        }

        return true;
    }
}
