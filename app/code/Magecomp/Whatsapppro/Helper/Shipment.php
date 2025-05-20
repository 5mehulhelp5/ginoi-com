<?php
namespace Magecomp\Whatsapppro\Helper;

use Magento\Store\Model\ScopeInterface;

class Shipment extends \Magecomp\Whatsapppro\Helper\Data
{
    // USER TEMPLATE
    const SMS_IS_CUSTOMER_SHIPMENT_NOTIFICATION = 'usertemplate/usershipment/enable';
    const SMS_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE = 'usertemplate/usershipment/template';
    const CSID_SMS_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE = 'usertemplate/usershipment/contentsid';

    //ADMIN TEMPLATE
    const SMS_IS_ADMIN_SHIPMENT_NOTIFICATION = 'admintemplate/adminshipment/enable';
    const SMS_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE = 'admintemplate/adminshipment/template';
    const CSID_SMS_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE = 'admintemplate/adminshipment/contentsid';

    public function isShipmentNotificationForUser($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->isEnabledWhatsapp($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_SHIPMENT_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShipmentNotificationUserTemplate($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getShipmentNotificationUserSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getShipmentNotificationForAdminSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return $this->scopeConfig->getValue(
                self::CSID_SMS_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isShipmentNotificationForAdmin($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->isEnabledWhatsapp($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_ADMIN_SHIPMENT_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShipmentNotificationForAdminTemplate($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId)) {
            return $this->scopeConfig->getValue(
                self::SMS_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
}
