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
 * @category   Mageplaza
 * @package    Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoiceFix\Helper;

/**
 * Class PrintProcess
 * @package Mageplaza\PdfInvoice\Helper
 */
class PrintProcess extends \Mageplaza\PdfInvoice\Helper\PrintProcess
{
    public function getDataProcess($type, $id = null)
    {
        $originalData = parent::getDataProcess($type, $id);
        $order = $originalData['order'];
        $frontendStatusLabel = $order->getFrontendStatusLabel();
        $order->setStatus($frontendStatusLabel);
        $originalData['order'] = $order;
        $originalData['order_data']['is_not_virtual']    = $order->getIsNotVirtual();

        return $originalData;
    }
}

