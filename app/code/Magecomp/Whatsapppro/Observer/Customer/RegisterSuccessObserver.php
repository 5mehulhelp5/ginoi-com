<?php
namespace Magecomp\Whatsapppro\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class RegisterSuccessObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercustomer;
    protected $emailfilter;
    protected $_customerModel;

    public function __construct(
        \Magecomp\Whatsapppro\Helper\Apicall $helperapi,
        \Magecomp\Whatsapppro\Helper\Customer $helpercustomer,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\Customer $customerModel
    ) {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
        $this->emailfilter = $filter;
        $this->_customerModel = $customerModel;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helpercustomer->isEnabled()) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();

        $controller = $observer->getAccountController();

        $mobilenumber = $controller->getRequest()->getParam('mobilenumber');
        $this->emailfilter->setVariables([
            'customer' => $customer,
            'mobilenumber' => $mobilenumber
        ]);

        $tempcustomer =  $this->_customerModel->load($customer->getId());

        $json = json_encode([
            'firstname' => $tempcustomer->getData('firstname'),
            'lastname' => $tempcustomer->getData('lastname'),
            'email' => $tempcustomer->getData('email'),
            'created_in' => (string)$tempcustomer->getData('created_at'),
            'created_at' => (string)$tempcustomer->getData('created_at'),
            'mobilenumber' => $mobilenumber
        ]);

        if ($this->helpercustomer->isSignUpNotificationForAdmin($storeId=null) && $this->helpercustomer->getAdminNumber($storeId=null)) {
            $message = $this->helpercustomer->getSignUpNotificationForAdminTemplate($storeId=null);
            $finalmessage = $this->emailfilter->filter($message);
            $csid = $this->helpercustomer->getSignUpNotificationForAdminSID($storeId=null);
            $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber(), $finalmessage,$storeId=null,$json,$csid);
        }
        if ($mobilenumber == '' || $mobilenumber == null) {
            return $this;
        }

        if ($this->helpercustomer->isSignUpNotificationForUser($storeId=null)) {
            $message = $this->helpercustomer->getSignUpNotificationForUserTemplate($storeId=null);
            $finalmessage = $this->emailfilter->filter($message);
            $csid = $this->helpercustomer->getSignUpNotificationForUserSID($storeId=null);
            $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId=null,$json,$csid);
        }
        return $this;
    }
}
