<?php
namespace Magecomp\Whatsapppro\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class OrderSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helperorder;
    protected $emailfilter;
    protected $customerFactory;

    public function __construct(
        \Magecomp\Whatsapppro\Helper\Apicall $helperapi,
        \Magecomp\Whatsapppro\Helper\Order $helperorder,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->helperapi = $helperapi;
        $this->helperorder = $helperorder;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$this->helperorder->isEnabledWhatsapp($order->getStoreId())) {
            return $this;
        }
        if ($order) {
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
                    'customer' => $customer,
                    'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);

                $data['mobilenumber'] = $mobilenumber;

                $data['customer_firsname'] = (string)$customer->getFirstname();
                $data['customer_lastname'] = (string)$customer->getLastname();
                $data['customer_email'] = (string)$customer->getEmail();
                $data['customer_created_at'] = (string)$customer->getCreatedAt();

            } else {
                $this->emailfilter->setVariables([
                    'order' => $order,
                    'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);
                $data['mobilenumber'] = $mobilenumber;
            }

            $json = json_encode($data);

            if ($this->helperorder->isOrderNotificationForUser($storeId)) {
                $message = $this->helperorder->getOrderNotificationUserTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $sid = $this->helperorder->getOrderNotificationUserSID($storeId);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId,$json,$sid);
            }

            if ($this->helperorder->isOrderNotificationForAdmin($storeId) && $this->helperorder->getAdminNumber($storeId)) {
                $message = $this->helperorder->getOrderNotificationForAdminTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $sid = $this->helperorder->getOrderNotificationForAdminSID($storeId);
                $this->helperapi->callApiUrl($this->helperorder->getAdminNumber(), $finalmessage,$storeId,$json,$sid);
            }
        }
        return $this;
    }
}
