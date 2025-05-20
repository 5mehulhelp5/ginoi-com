<?php
namespace Magecomp\Whatsapppro\Model;

use Magento\Framework\Exception\AuthenticationException;

class Whatsapppropost implements \Magecomp\Whatsapppro\Api\WhatsappproInterface
{
    protected $helperapi;
    protected $helperorder;
    protected $helpershipment;
    protected $helpercreditmemo;
    protected $emailfilter;
    protected $customerFactory;
    protected $orderRepository;
    protected $helperinvoice;
    protected $shipmentRepository;
    protected $creditmemoRepository;
    protected $helpercontact;
    protected $helpercustomer;
    protected $customerRepositoryInterface;
    protected $invoiceRepository;


    public function __construct(
        \Magecomp\Whatsapppro\Helper\Apicall $helperapi,
        \Magecomp\Whatsapppro\Helper\Order $helperorder,
        \Magecomp\Whatsapppro\Helper\Invoice $helperinvoice,
        \Magecomp\Whatsapppro\Helper\Shipment $helpershipment,
        \Magecomp\Whatsapppro\Helper\Creditmemo $helpercreditmemo,
        \Magecomp\Whatsapppro\Helper\Contact $helpercontact,
        \Magecomp\Whatsapppro\Helper\Customer $helpercustomer,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->helperapi = $helperapi;
        $this->helperorder = $helperorder;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
        $this->helpercontact = $helpercontact;
        $this->helperinvoice = $helperinvoice;
        $this->orderRepository = $orderRepository;
        $this->helpercustomer = $helpercustomer;
        $this->invoiceRepository = $invoiceRepository;
        $this->helpershipment = $helpershipment;
        $this->shipmentRepository = $shipmentRepository;
        $this->helpercreditmemo = $helpercreditmemo;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function sendOrderNotification($orderid)
    {
        try {
            if (empty($orderid)) {
                $response = ["status"=>false, "message"=>__("Please, Enter Order Id.")];
                return json_encode($response);
            }
            $order = $this->orderRepository->get($orderid);
            $storeId= $order->getStoreId();
            if (!$this->helperorder->isEnabledWhatsapp($storeId)) {
                $response = [
                   "status"=>false,
                   'message' => __("This service is disable right now.")
                ];
                return json_encode($response);
            }
   
            if ($order) {
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
                        'customer' => $customer,
                        'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                        'mobilenumber' => $mobilenumber
                    ]);
                } else {
                    $this->emailfilter->setVariables([
                        'order' => $order,
                        'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                        'mobilenumber' => $mobilenumber
                    ]);
                }

                if ($this->helperorder->isOrderNotificationForUser($storeId)) {
                    $message = $this->helperorder->getOrderNotificationUserTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId);
                }

                if ($this->helperorder->isOrderNotificationForAdmin($storeId) && $this->helperorder->getAdminNumber($storeId)) {
                    $message = $this->helperorder->getOrderNotificationForAdminTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($this->helperorder->getAdminNumber($storeId), $finalmessage,$storeId);
                }
                $response = ['status' => true,'message' => __("Order is Notified")];
            }
            return json_encode($response);
        } catch (\Exception $e) {
              throw new AuthenticationException(__($e->getMessage()));
        }
    }

    public function sendInvoiceNotification($invoiceid)
    {
        try {
            if (empty($invoiceid)) {
                $response = ["status"=>false, "message"=>__("Please, Enter Invoice Id.")];
                return json_encode($response);
            }
            $invoice = $this->invoiceRepository->get($invoiceid);
            $order = $invoice->getOrder();
            $storeId= $order->getStoreId();
            if (!$this->helperinvoice->isEnabledWhatsapp($storeId)) {
                $response = [
                   "status"=>false,
                   'message' => __("This service is disable right now.")
                ];
                return json_encode($response);
            }
            if ($invoice) {
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
                } else {
                    $this->emailfilter->setVariables([
                        'order' => $order,
                        'invoice' => $invoice,
                        'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                        'mobilenumber' => $mobilenumber
                    ]);
                }

                if ($this->helperinvoice->isInvoiceNotificationForUser($storeId)) {
                    $message = $this->helperinvoice->getInvoiceNotificationUserTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId);
                }

                if ($this->helperinvoice->isInvoiceNotificationForAdmin($storeId) && $this->helperinvoice->getAdminNumber($storeId)) {
                    $message = $this->helperinvoice->getInvoiceNotificationForAdminTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($this->helperinvoice->getAdminNumber($storeId), $finalmessage,$storeId);
                }
                $response = ['status' => true,'message' => __("Invoice is Notified")];
            }
            return json_encode($response);
        } catch (\Exception $e) {
              throw new AuthenticationException(__($e->getMessage()));
        }
    }

    public function sendShipmentNotification($shipmentid)
    {
        try {
            if (empty($shipmentid)) {
                $response = ["status"=>false, "message"=>__("Please, Enter Shipment Id.")];
                return json_encode($response);
            }
            $shipment = $this->shipmentRepository->get($shipmentid);
            $order = $shipment->getOrder();
            $storeId= $order->getStoreId();
            if (!$this->helpershipment->isEnabledWhatsapp($storeId)) {
                $response = [
                   "status"=>false,
                   'message' => __("This service is disable right now.")
                ];
                return json_encode($response);
            }
            if ($shipment) {
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
                        'shipment' => $shipment,
                        'customer' => $customer,
                        'mobilenumber' => $mobilenumber
                    ]);
                } else {
                    $this->emailfilter->setVariables([
                        'order' => $order,
                        'shipment' => $shipment,
                        'mobilenumber' => $mobilenumber
                    ]);
                }

                if ($this->helpershipment->isShipmentNotificationForUser($storeId)) {
                    $message = $this->helpershipment->getShipmentNotificationUserTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId);
                }

                if ($this->helpershipment->isShipmentNotificationForAdmin($storeId) && $this->helpershipment->getAdminNumber($storeId)) {
                    $message = $this->helpershipment->getShipmentNotificationForAdminTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($this->helpershipment->getAdminNumber($storeId), $finalmessage,$storeId);
                }
            }
            $response = ['status' => true,'message' => __("Shipment is Notified")];
            return json_encode($response);
        } catch (\Exception $e) {
              throw new AuthenticationException(__($e->getMessage()));
        }
    }

    public function sendCreditmemoNotification($creditmemoid)
    {
        try {
            if (empty($creditmemoid)) {
                $response = ["status"=>false, "message"=>__("Please, Enter Creditmemo Id.")];
                return json_encode($response);
            }
            $creditmemo = $this->creditmemoRepository->get($creditmemoid);
            $order = $creditmemo->getOrder();
            $storeId= $order->getStoreId();
            if (!$this->helpercreditmemo->isEnabledWhatsapp($storeId)) {
                $response = [
                   "status"=>false,
                   'message' => __("This service is disable right now.")
                ];
                return json_encode($response);
            }

       
            if ($creditmemo) {
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
                        'creditmemo' => $creditmemo,
                        'customer' => $customer,
                        'mobilenumber' => $mobilenumber
                    ]);
                } else {
                    $this->emailfilter->setVariables([
                        'order' => $order,
                        'creditmemo' => $creditmemo,
                        'mobilenumber' => $mobilenumber
                    ]);
                }

                if ($this->helpercreditmemo->isCreditmemoNotificationForUser($storeId)) {
                    $message = $this->helpercreditmemo->getCreditmemoNotificationUserTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId);
                }

                if ($this->helpercreditmemo->isCreditmemoNotificationForAdmin($storeId) && $this->helpercreditmemo->getAdminNumber($storeId)) {
                    $message = $this->helpercreditmemo->getCreditmemoNotificationForAdminTemplate($storeId);
                    $finalmessage = $this->emailfilter->filter($message);
                    $this->helperapi->callApiUrl($this->helpercreditmemo->getAdminNumber($storeId), $finalmessage,$storeId);
                }
            }
            $response = ['status' => true,'message' => __("Creditmemo is Notified")];
            return json_encode($response);
        } catch (\Exception $e) {
              throw new AuthenticationException(__($e->getMessage()));
        }
    }
    public function sendContactNotification($name, $email, $mobilenumber, $comment,$storeId)
    {
        try {
            if ((empty($name) || empty($email)|| empty($storeId) || empty($mobilenumber) || empty($comment))) {
                $response = ["status"=>false, "message"=>__("Invalid parameter list.")];
                return json_encode($response);
            }
            if (!$this->helpercontact->isEnabledWhatsapp($storeId)) {
                $response = [
                    "status"=>false,
                    'message' => __("This service is disable right now.")
                ];
                return json_encode($response);
            }

            $this->emailfilter->setVariables([
            'name' => $name,
            'email' => $email,
            'telephone' => $mobilenumber,
            'comment' => $comment,
            'store_name' => $this->helpercontact->getStoreName()
            ]);

            if ($this->helpercontact->isContactNotificationForUser($storeId)) {
                $message = $this->helpercontact->getContactNotificationUserTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId);
            }

            if ($this->helpercontact->isContactNotificationForAdmin($storeId) && $this->helpercontact->getAdminNumber($storeId)) {
                $message = $this->helpercontact->getContactNotificationForAdminTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($this->helpercontact->getAdminNumber($storeId), $finalmessage,$storeId);
            }
            $response = ['status' => true,'message' => __("Contact Information is Notified")];
            return json_encode($response);
        } catch (\Exception $e) {
              throw new AuthenticationException(__($e->getMessage()));
        }
    }
    public function sendRegNotification($customerId,$storeId)
    {
        try {
            if ((empty($customerId) || empty($storeId))) {
                $response = ["status"=>false, "message"=>__("Invalid parameter list.")];
                return json_encode($response);
            }
            if (!$this->helpercustomer->isEnabledWhatsapp($storeId)) {
                $response = [
                    "status"=>false,
                    'message' => __("This service is disable right now.")
                ];
                return json_encode($response);
            }
          
             $customer = $this->customerRepositoryInterface->getById($customerId);
            if (!$customer->getId()) {
                $response = ["status"=>false, "message"=>__("Customer Is Not Valid")];
                return json_encode($response);
            }
            $mobilenumber='';
          if($customermobile = $customer->getCustomAttribute('mobilenumber')) {
                          $mobilenumber=$customermobile->getValue();
            }
    
            if ($mobilenumber == '' || $mobilenumber == null) {
                $response = [
                      "status"=>false,
                      'message' => __("Mobile Number Is Not Available")
                  ];
                  return json_encode($response);
            }
            $this->emailfilter->setVariables([
            'customer' => $customer,
            'mobilenumber' => $mobilenumber
            ]);

            if ($this->helpercustomer->isSignUpNotificationForAdmin($storeId) && $this->helpercustomer->getAdminNumber($storeId)) {
                $message = $this->helpercustomer->getSignUpNotificationForAdminTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber($storeId), $finalmessage,$storeId);
            }
        
            if ($this->helpercustomer->isSignUpNotificationForUser($storeId)) {
                $message = $this->helpercustomer->getSignUpNotificationForUserTemplate($storeId);
                $finalmessage = $this->emailfilter->filter($message);

                $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId);
            }
            $response = ['status' => true,'message' => __("Account Registration is Notified")];
            return json_encode($response);
        } catch (\Exception $e) {
              throw new AuthenticationException(__($e->getMessage()));
        }
    }
}
