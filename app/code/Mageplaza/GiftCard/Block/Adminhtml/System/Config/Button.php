<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Mageplaza\GiftCard\Helper\Data;

/**
 * Class Button
 * @package Mageplaza\GiftCard\Block\Adminhtml\System\Config
 */
class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/config/button.phtml';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Button constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @param $element
     *
     * @return string
     */
    public function render($element): string
    {
        $element->unsScope();

        return parent::render($element);
    }

    /**
     * @param $element
     *
     * @return string
     */
    protected function _getElementHtml($element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'button_url'   => $this->getUrl($originalData['button_url']),
                'html_id'      => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }
}
