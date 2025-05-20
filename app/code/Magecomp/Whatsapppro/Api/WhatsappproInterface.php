<?php
namespace Magecomp\Whatsapppro\Api;

/**
 * Interface WhatsappproInterface
 * Magecomp\Whatsapppro\Api
 */
interface WhatsappproInterface
{
    
    /**
     * Send Order Notification
     *
     * @param int $orderid
     * @return string
     */
    public function sendOrderNotification(
        $orderid
    );

    /**
     * Send Invoice Notification
     *
     * @param int $invoiceid
     * @return string
     */
    public function sendInvoiceNotification(
        $invoiceid
    );

    /**
     * Send Shipment Notification
     *
     * @param int $shipmentid
     * @return string
     */
    public function sendShipmentNotification(
        $shipmentid
    );

    /**
     * Send Creditmemo Notification
     *
     * @param int $creditmemoid
     * @return string
     */
    public function sendCreditmemoNotification(
        $creditmemoid
    );

    /**
     * Send Contact Notification
     *
     * @param string $name
     * @param string $email
     * @param string $mobilenumber
     * @param string $comment
     * @param int $storeId
     * @return string
     */
    public function sendContactNotification(
        $name,
        $email,
        $mobilenumber,
        $comment,
        $storeId
    );

    /**
     * Send Registration Notification
     *
     * @param int $customerId
     * @param int $storeId
     * @return string
     */
    public function sendRegNotification(
        $customerId,$storeId
    );
}
