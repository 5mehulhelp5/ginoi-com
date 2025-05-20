<?php
/**
 * Copyright © 2022 Iksanika. All rights reserved.
 * See IKS-COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Iksanika\Productmanage\Plugin\Catalog\Model;

// @TODO: findout how to change declaration to pluging system
class Product // extends \Magento\Catalog\Model\Product
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
    public function aroundToXml(\Magento\Catalog\Model\Product $subject, \Closure $proceed, array $keys = [], $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        $xml = '';
        $data = $subject->toArray($keys);
        foreach ($data as $fieldName => $fieldValue) {

            $fieldValue = is_array($fieldValue) ? implode(',', $fieldValue) : $fieldValue;

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
