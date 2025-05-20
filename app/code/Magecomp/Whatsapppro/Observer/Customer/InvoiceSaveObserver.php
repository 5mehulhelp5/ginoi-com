<?php
namespace Magecomp\Whatsapppro\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class InvoiceSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helperinvoice;
    protected $emailfilter;
    protected $customerFactory;

    public function __construct(
        \Magecomp\Whatsapppro\Helper\Apicall $helperapi,
        \Magecomp\Whatsapppro\Helper\Invoice $helperinvoice,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->helperapi = $helperapi;
        $this->helperinvoice = $helperinvoice;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice    = $observer->getInvoice();
        $order      = $invoice->getOrder();
        if (!$this->helperinvoice->isEnabledWhatsapp($order->getStoreId())) {
            return $this;
        }
        if ($invoice) {
            $data = [
                'order_id' => (string)$order->getId(),
                'order_state' => (string)$order->getState(),
                'order_status' => (string)$order->getStatus(),
                'order_coupon_code' => (string)$order->getCouponCode(),
                'order_shipping_description' => $order->getShippingDescription(),
                'order_base_grand_total' => (string)$order->getBaseGrandTotal(),
                'order_base_shipping_amount' => (string) $order->getBaseShippingAmount(),
                'order_base_subtotal' => (string)$order->getBaseSubtotal(),
                'order_base_tax_amount' => (string)$order->getBaseTaxAmount(),
                'order_discount_amount' => (string)$order->getDiscountAmount(),
                'order_grand_total' => (string)$order->getGrandTotal(),
                'order_shipping_amount' => (string)$order->getShippingAmount(),
                'order_shipping_tax_amount' => (string)$order->getShippingTaxAmount(),
                'order_subtotal' => (string)$order->getSubtotal(),
                'order_tax_amount' => (string)$order->getTaxAmount(),
                'order_total_qty_ordered' => (string)$order->getTotalQtyOrdered(),
                'order_increment_id' => (string)$order->getIncrementId(),
                'order_customer_email' => (string)$order->getCustomerEmail(),
                'order_customer_firstname' => (string)$order->getCustomerFirstname(),
                'order_customer_lastname' => (string)$order->getCustomerLastname(),
                'order_currency_code' => (string)$order->getOrderCurrencyCode(),
                'order_store_name' => (string)$order->getStoreName(),
                'order_created_at' => (string)$order->getCreatedAt(),
                'order_order_total' => (string)$order->formatPriceTxt($order->getGrandTotal())
            ];
            
            $storeId= $order->getStoreId();
            $billingAddress = $order->getBillingAddress();
            $mobilenumber = $billingAddress->getTelephone();

            if ($order->getCustomerId() > 0) {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $mobile = $customer->getMobilenumber();
                if ($mobile != '' && $mobile != null) {
                    $mobilenumber = $mobile;
                }

                $this->emailfilter->setVariables([
                    'order' => $order,
                    'invoice' => $invoice,
                    'customer' => $customer,
                    'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);
                
                $data['mobilenumber'] = $mobilenumber;

                $data['customer_firsname'] = $customer->getFirstname();
                $data['customer_lastname'] = $customer->getLastname();
                $data['customer_email'] = $customer->getEmail();
                $data['customer_created_at'] = $customer->getCreatedAt();

            } else {
                $this->emailfilter->setVariables([
                    'order' => $order,
                    'invoice' => $invoice,
                    'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);
                $data['mobilenumber'] = $mobilenumber;
            }
            $data['invoice_id'] = (string)$invoice->getId();
            $data['invoice_tax_amount'] = (string)$invoice->getTaxAmount();
            $data['invoice_base_grand_total'] = (string)$invoice->getBaseGrandTotal();
            $data['invoice_subtotal'] = (string)$invoice->getSubtotal();
            $data['invoice_grand_total'] = (string)$invoice->getGrandTotal();
            $data['invoice_shipping_amount'] = (string)$invoice->getShippingAmount();
            $data['invoice_shipping_tax_amount'] = (string)$invoice->getShippingTaxAmount();
            $data['invoice_total_qty'] = (string)$invoice->getTotalQty();
            $data['invoice_order_id'] = (string)$invoice->getOrderId();
            $data['invoice_increment_id'] = (string)$invoice->getIncrementId();
            $data['invoice_created_at'] = (string)$invoice->getCreatedAt();

            $json = json_encode($data);
            
            if ($this->helperinvoice->isInvoiceNotificationForUser($storeId)) {
                $message = $this->helperinvoice->getInvoiceNotificationUserTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $csid = $this->helperinvoice->getInvoiceNotificationUserSID($storeId);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
            }

            if ($this->helperinvoice->isInvoiceNotificationForAdmin($storeId) && $this->helperinvoice->getAdminNumber($storeId)) {
                $message = $this->helperinvoice->getInvoiceNotificationForAdminTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $csid = $this->helperinvoice->getInvoiceNotificationForAdminSID($storeId);
                $this->helperapi->callApiUrl($this->helperinvoice->getAdminNumber($storeId), $finalmessage,$storeId,$json,$csid);
            }
        }
        return $this;
    }
}
