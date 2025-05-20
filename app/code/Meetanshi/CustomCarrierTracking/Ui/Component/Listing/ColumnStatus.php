<?php

namespace Meetanshi\CustomCarrierTracking\Ui\Component\Listing;

class ColumnStatus implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Active')],
            ['value' => 0, 'label' => __('Inactive')]
        ];
    }
}