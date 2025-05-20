<?php
/**
 * Copyright © 2022 Iksanika. All rights reserved.
 * See IKS-COPYING.txt for license details.
 */

namespace Iksanika\Productmanage\Model;

class Product extends \Magento\Catalog\Model\Product
{
    /**
     * Convert object data into XML string
     *
     * @param array $keys array of keys that must be represented
     * @param string $rootName root node name
     * @param bool $addOpenTag flag that allow to add initial xml node
     * @param bool $addCdata flag that require wrap all values in CDATA
     * @return string
     */
    public function toXml(array $keys = [], $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
//die('~~~ iksanika: productmanage: aroundToXml 2 ~~~');
        $xml = '';
        $data = $this->toArray($keys);
        foreach ($data as $fieldName => $fieldValue) {
            if ($addCdata === true) {
                $fieldValue = "<![CDATA[{$fieldValue}]]>";
            } else {
                $fieldValue = str_replace(
                    ['&', '"', "'", '<', '>'],
                    ['&amp;', '&quot;', '&apos;', '&lt;', '&gt;'],
                    $fieldValue
                );
            }
            $xml .= "<{$fieldName}>{$fieldValue}</{$fieldName}>\n";
        }
        if ($rootName) {
            $xml = "<{$rootName}>\n{$xml}</{$rootName}>\n";
        }
        if ($addOpenTag) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }
        return $xml;
    }
}
