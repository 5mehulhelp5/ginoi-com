<?php

namespace Magecomp\Whatsappcontact\Model\System\Config\Source;

class Effact
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('none')],
            ['value' => 'tada', 'label' => __('Tada')],
            ['value' => 'grow', 'label' => __('Grow')],
            ['value' => 'shrik', 'label' => __('Shrik')],
            ['value' => 'rotate', 'label' => __('Rotate')],
            ['value' => 'buzz', 'label' => __('Buzz')]
        ];
    }
}
