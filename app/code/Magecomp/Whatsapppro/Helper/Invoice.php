<?php
namespace Magecomp\Whatsapppro\Helper;

use Magento\Store\Model\ScopeInterface;

class Invoice extends \Magecomp\Whatsapppro\Helper\Data
{
    // USER TEMPLATE
    const SMS_IS_CUSTOMER_INVOICE_NOTIFICATION = 'usertemplate/userinvoice/enable';
    const SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE = 'usertemplate/userinvoice/template';
    const CSID_SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE = 'usertemplate/userinvoice/contentsid';

    //ADMIN TEMPLATE
    const SMS_IS_ADMIN_INVOICE_NOTIFICATION = 'admintemplate/admininvoice/enable';
    const SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE = 'admintemplate/admininvoice/template';
    const CSID_SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE = 'admintemplate/admininvoice/contentsid';

    public function isInvoiceNotificationForUser($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->isEnabledWhatsapp($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_INVOICE_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceNotificationUserTemplate($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getInvoiceNotificationUserSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getInvoiceNotificationForAdminSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isInvoiceNotificationForAdmin($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->isEnabledWhatsapp($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_ADMIN_INVOICE_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceNotificationForAdminTemplate($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
}
