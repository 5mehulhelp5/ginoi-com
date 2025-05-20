<?php
namespace Magecomp\Whatsapppro\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class ShipmentSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpershipment;
    protected $emailfilter;
    protected $customerFactory;

    public function __construct(
        \Magecomp\Whatsapppro\Helper\Apicall $helperapi,
        \Magecomp\Whatsapppro\Helper\Shipment $helpershipment,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->helperapi = $helperapi;
        $this->helpershipment = $helpershipment;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment   = $observer->getShipment();
        $order      = $shipment->getOrder();

        if (!$this->helpershipment->isEnabledWhatsapp($order->getStoreId())) {
            return $this;
        }
        if ($shipment) {
            $storeId= $order->getStoreId();
            $billingAddress = $order->getBillingAddress();
            $mobilenumber = $billingAddress->getTelephone();
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
            
            if ($order->getCustomerId() > 0) {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $mobile = $customer->getMobilenumber();
                if ($mobile != '' && $mobile != null) {
                    $mobilenumber = $mobile;
                }

                $this->emailfilter->setVariables([
                    'order' => $order,
                    'shipment' => $shipment,
                    'customer' => $customer,
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
                    'shipment' => $shipment,
                    'mobilenumber' => $mobilenumber
                ]);
                $data['mobilenumber'] = $mobilenumber;
            }
            $data['shipment_id'] = (string)$shipment->getId();
            $data['shipment_increment_id'] = (string)$shipment->getIncrementId();
            $data['shipment_total_qty'] = (string)$shipment->getTotalQty();
            $data['shipment_order_id'] = (string)$shipment->getOrderId();
            $data['shipment_customer_note'] = (string)$shipment->getCustomerNote();

            $json = json_encode($data);

            if ($this->helpershipment->isShipmentNotificationForUser($storeId)) {
                $message = $this->helpershipment->getShipmentNotificationUserTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $csid = $this->helpershipment->getShipmentNotificationUserSID($storeId);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$csid);
            }

            if ($this->helpershipment->isShipmentNotificationForAdmin($storeId) && $this->helpershipment->getAdminNumber($storeId)) {
                $message = $this->helpershipment->getShipmentNotificationForAdminTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $csid = $this->helpershipment->getShipmentNotificationForAdminSID($storeId);
                $this->helperapi->callApiUrl($this->helpershipment->getAdminNumber($storeId), $finalmessage,$storeId,$json,$csid);
            }
        }
        return $this;
    }
}
