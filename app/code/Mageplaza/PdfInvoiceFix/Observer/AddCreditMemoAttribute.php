<?php

namespace Mageplaza\PdfInvoiceFix\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCreditMemoAttribute implements ObserverInterface
{
    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $transportObject = $observer->getData('transportObject');

        $order = $transportObject['order'];
        $price = $order->formatPriceTxt($order->getShippingAmount());
        $transportObject->setData('shipping_amount', $price);
    }
}
