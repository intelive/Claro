<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template;

class ProductLastId extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $syncResourceModel;

    /**
     * ProductLastId constructor.
     * @param Template\Context $context
     * @param \Intelive\Claro\Model\ResourceModel\ClaroReportsSync $syncResourceModel
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Intelive\Claro\Model\ResourceModel\ClaroReportsSync $syncResourceModel,
        array $data = []
    )
    {
        $this->syncResourceModel = $syncResourceModel;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        return $this->syncResourceModel->getLastIdDate('product');
    }
}