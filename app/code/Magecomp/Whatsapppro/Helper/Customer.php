<?php
namespace Magecomp\Whatsapppro\Helper;

use Magento\Store\Model\ScopeInterface;

class Customer extends \Magecomp\Whatsapppro\Helper\Data
{
    // USER TEMPLATE
    const SMS_IS_CUSTOMER_SIGNUP_NOTIFICATION = 'usertemplate/usersignup/enable';
    const SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE = 'usertemplate/usersignup/template';
    const SID_SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE = 'usertemplate/usersignup/contentsid';

    //ADMIN TEMPLATE
    const SMS_IS_ADMIN_SIGNUP_NOTIFICATION = 'admintemplate/adminsignup/enable';
    const SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE = 'admintemplate/adminsignup/template';
    const SID_SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE = 'admintemplate/adminsignup/contentsid';
  
    public function isSignUpNotificationForAdmin($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->isEnabledWhatsapp($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_ADMIN_SIGNUP_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid()
        );
    }

    public function getSignUpNotificationForAdminTemplate($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
    }
    public function getSignUpNotificationForAdminSID($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SID_SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
    }
    public function getSignUpNotificationForUserSID($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SID_SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
    }

    public function isSignUpNotificationForUser($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->isEnabledWhatsapp($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_SIGNUP_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid()
        );
    }

    public function getSignUpNotificationForUserTemplate($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
    }
}
