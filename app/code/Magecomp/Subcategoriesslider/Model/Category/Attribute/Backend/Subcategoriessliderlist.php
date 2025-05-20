<?php

namespace Magecomp\Subcategoriesslider\Model\Category\Attribute\Backend;
class Subcategoriessliderlist extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'Subcategoriessliderlist') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data) ?: null);
        }
        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        }
        return $this;
    }

    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'Subcategoriessliderlist') {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (!is_array($data)) {
                    $object->setData($attributeCode, explode(',', $data));
                } else {
                    $object->setData($attributeCode, $data);
                }

            }
        }
        return $this;
    }
}
