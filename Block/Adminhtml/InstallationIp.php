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

class InstallationIp extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * InstallationIp constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        return $_SERVER['SERVER_ADDR'];
    }
}