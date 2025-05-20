<?php
namespace Magecomp\Whatsapppro\Model\Config\Otp;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Numeric')],
            ['value' => 1, 'label' => __('Alpha Numeric')],
        ];
    }
}
