<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class AttachmentDisplayOptions implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Do Not Display'),
                'value' => 'no'
            ],
            [
                'label' => __('On Shipping '),
                'value' => 'after-shipping-methods'
            ],
            [
                'label' => __('On Payment '),
                'value' => 'after-payment-methods'
            ]
        ];
    }
}
