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
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoiceFix\Block;

use Mageplaza\PdfInvoice\Model\Source\Type;

/**
 * Class PdfItems
 * @package Mageplaza\PdfInvoice\Block
 */
class PdfItems extends \Mageplaza\PdfInvoice\Block\PdfItems
{
    public function getThHtml($key, $a4Barcode): string
    {
        $order = $this->getOrder();
        $store = $order->getStore();
        if ($key === 'Items') {
            if ($a4Barcode) {
                $html = '<div class="mp-item-bc">';
                $html .= '<span>' . __('%1', __($key)) . '</span>';
                $html .= '</div>';
                $html .= '<div class="mp-barcode-bc">';
                $html .= '<span></span>';
                $html .= '</div>';

                return $html;
            } else {
                $html = '<div class="mp-item">';
                $html .= '<span>' . __('%1', __($key)) . '</span>';
                $html .= '</div>';

                return $html;
            }
        }

        $classForEl = $a4Barcode ? 'mp-'.strtolower($key).'-bc' : 'mp-'.strtolower($key);
        $html = '<div class="'.$classForEl.'">';

        $key = $this->formatColumnTh($store['code'],$key);

        $html .= '<span>' . __('%1', __($key)) . '</span>';
        $html .= '</div>';

        return $html;
    }

    function formatColumnTh($code,$key)
    {
        if($code !== 'us'){
            if ($key === 'Price') {
                return "Precio Unitario";
            }
            if ($key === 'Subtotal') {
                return "Monto";
            }
        }
        return $key;
    }

    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku')) {
            return $item->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    public function getItemOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    public function getOrderType()
    {
        return Type::ORDER;
    }
}

