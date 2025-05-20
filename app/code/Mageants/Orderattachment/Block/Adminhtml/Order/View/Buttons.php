<?php
/**
 *
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Block\Adminhtml\Order\View;
    
use Magento\Sales\Block\Adminhtml\Order\View;

class Buttons extends View
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->getOrderId()) {
            return $this;
        }

        $buttonUrl = $this->_urlBuilder->getUrl(
            'orderattachment/attachment/SendMail',
            ['order_id' => $this->getOrderId()]
        );
        $buttonUrl1 = 'https://www.google.com/';

        $this->addButton(
            'create_custom_button',
            ['label' => __('Send Attachments Email'), 'onclick' => 'setLocation(\'' . $buttonUrl . '\')']
        );
        
        return $this;
    }
}
