<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Helper;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Model\GiftCard\Action;
use Mageplaza\GiftCard\Model\GiftCard\Status;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\Product\DeliveryMethods;

/**
 * Class GiveGiftCard
 * @package Mageplaza\GiftCard\Helper
 */
class GiveGiftCard extends Data
{
    /**
     * ORDER
     */
    public const ORDER = 'order';

    /**
     * INVOICE
     */
    public const INVOICE = 'invoice';

    /**
     * CREDIT_MEMO
     */
    public const CREDIT_MEMO = 'creditmemo';

    /**
     * SHIPMENT
     */
    public const SHIPMENT = 'shipment';

    /**
     * @var GiftCardFactory
     */
    protected $_giftCardFactory;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var InvoiceCollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var CreditmemoCollectionFactory
     */
    protected $creditmemoCollectionFactory;

    /**
     * @var OrderItemCollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Address
     */
    private $addressToValidate;

    /**
     * @var false
     */
    private $condition;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param GiftCardFactory $giftCardFactory
     * @param QuoteFactory $quoteFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param CreditmemoCollectionFactory $creditmemoCollectionFactory
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        GiftCardFactory $giftCardFactory,
        QuoteFactory $quoteFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        CreditmemoCollectionFactory $creditmemoCollectionFactory,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        RuleFactory $ruleFactory
    ) {
        $this->_giftCardFactory            = $giftCardFactory;
        $this->_quoteFactory               = $quoteFactory;
        $this->invoiceCollectionFactory    = $invoiceCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->orderItemCollectionFactory  = $orderItemCollectionFactory;
        $this->shipmentCollectionFactory   = $shipmentCollectionFactory;
        $this->ruleFactory                 = $ruleFactory;

        parent::__construct($context, $objectManager, $storeManager, $localeDate, $customerSession);
    }

    /**
     * @param $order
     *
     * @return $this
     * @throws LocalizedException
     */
    public function generateGiveGiftCode($order)
    {

        if (!$this->checkConditionGiveCard($order)) {
            return $this;
        }

        if ($order['give_gift_cards']) {
            return $this;
        }
        $storeId      = $order ? $order->getStoreId() : $order->getStoreId();
        $countGiftCardUse = 0;
        if(!empty($this->getCountGiftCardUse($order->getStoreId()))){
            $countGiftCardUse = 1;
        }
        $giftCardData = [
            'pattern'               => $this->getCodePattern($storeId),
            'balance'               => $this->getGiveGiftCardConfig('balance', $storeId),
            'status'                => Status::STATUS_ACTIVE,
            'can_redeem'            => $this->allowRedeemGiftCard(),
            'store_id'              => $storeId,
            'expire_after'          => $this->getExpireAfterDay($storeId),
            'template_id'           => 0,
            'image'                 => '',
            'template_fields'       => [
                'sender'    => $order->getCustomerName(),
                'recipient' => '',
                'message'   => ''
            ],
            'order_item_id'         => $order->getId(),
            'conditions_serialized' => '',
            'customer_ids'          => $order->getCustomerId(),
            'order_increment_id'    => $order->getIncrementId(),
            'action_vars'           => [
                'auth'               => $order->getCustomerName(),
                'order_increment_id' => $order->getIncrementId()
            ],
            'delivery_method'       => DeliveryMethods::METHOD_EMAIL,
            'delivery_address'      => $order->getCustomerEmail(),
            'is_sent'               => true,
            'delivery_date'         => '',
            'timezone'              => '',
            'extra_content'         => '',
            'description'           => '',
            'is_can_refund'        => $this->getIsCanRefund($order->getStoreId()),
            'count_giftcard_use'    => $countGiftCardUse,
            'is_config_refund'      => 1
        ];

        $giftCardData['send_to_recipient'] = true;

        try {
            $giftCard = $this->_giftCardFactory->create();
            $giftCard->addData($giftCardData)->save();
            $order->setData('give_gift_cards', $giftCard->getGiftcardId());
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $order
     *
     * @return bool
     * @throws LocalizedException
     */
    public function checkConditionGiveCard($order = null)
    {
        $storeId = $order ? $order->getStoreId() : $this->getStoreId();
        if (!$this->getGiveGiftCardConfig('give_giftcard', $storeId)) {
            $this->condition = false;

            return $this->condition;
        }
        $checkCondition  = false;
        $conditionConfig = $this->getGiveGiftCardConfig('condition', $storeId);
        $quote           = $this->_quoteFactory->create()->load($order->getQuoteId());
        if ($conditionConfig && $this->getGiveGiftCardConfig('give_giftcard', $storeId)) {
            $rule = $this->ruleFactory->create();
            $rule->setConditionsSerialized($conditionConfig);
            $address        = $this->getAddressToValidate($quote, $order);
            $checkCondition = $rule->getConditions()->validate($address);
        }

        return $checkCondition;
    }

    /**
     * @param Quote $quote
     * @param $order
     *
     * @return Address
     */
    protected function getAddressToValidate(Quote $quote, $order)
    {
        if (!$this->addressToValidate) {
            $this->addressToValidate
                        = clone($quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress());
            $collection = null;
            if ($order->getEntityType() === self::CREDIT_MEMO) {
                $collection = $this->creditmemoCollectionFactory->create();
            } else {
                if ($order->getEntityType() === self::INVOICE) {
                    $collection = $this->invoiceCollectionFactory->create();
                } else {
                    if ($order->getEntityType() === self::ORDER) {
                        $collection = $this->orderItemCollectionFactory->create();
                    } else {
                        if ($order->getEntityType() === self::SHIPMENT) {
                            $collection = $this->shipmentCollectionFactory->create();
                        }
                    }
                }
            }

            $this->addressToValidate->addData($this->getTotalProductQuantityInInvoices($order, $quote, $order->getId(),
                $collection));
        }

        return $this->addressToValidate;
    }

    /**
     * @param $order
     * @param $quote
     * @param $orderId
     * @param null $collection
     *
     * @return array
     */
    public function getTotalProductQuantityInInvoices($order, $quote, $orderId, $collection = null)
    {
        $fieldValue = [];

        if (!isset($collection)) {
            return $fieldValue;
        }

        $fieldValue = array_merge($fieldValue, $quote->getData());
        $fieldValue = array_merge($fieldValue, $order->getData());
        if ($order->getBillingAddress() !== null) {
            $fieldValue = array_merge($fieldValue, $order->getBillingAddress()->getData());
        }
        if ($order->getShippingAddress() !== null) {
            $fieldValue = array_merge($fieldValue, $order->getShippingAddress()->getData());
        }
        $collection->addAttributeToFilter('order_id', $orderId);

        $fieldValue['grand_total']       = array_sum($collection->getColumnValues('grand_total')) ?? 0;
        $fieldValue['tax_amount']        = array_sum($collection->getColumnValues('tax_amount')) ?? 0;
        $fieldValue['shipping_amount']   = array_sum($collection->getColumnValues('shipping_amount')) ?? 0;
        $fieldValue['shipping_incl_tax'] = array_sum($collection->getColumnValues('shipping_amount')) ?? 0;
        $fieldValue['subtotal_incl_tax'] = array_sum($collection->getColumnValues('subtotal_incl_tax')) ?? 0;
        $fieldValue['total_qty']         = array_sum($collection->getColumnValues('total_qty')) ?? 0;
        $fieldValue['subtotal']          = array_sum($collection->getColumnValues('subtotal')) ?? 0;
        $fieldValue['base_subtotal']     = array_sum($collection->getColumnValues('base_subtotal')) ?? 0;
        $fieldValue['discount_amount']   = array_sum($collection->getColumnValues('discount_amount')) ?? 0;

        $this->_eventManager->dispatch('mpgiftcard_add_value_field_condition',
            ['fieldValue' => $fieldValue, 'collection' => $collection]);

        $collection->clear();

        return $fieldValue;
    }

    /**
     * @param $giftCardId
     * @param Order $order
     *
     * @return void
     * @throws Exception
     */
    public function refundGiveGiftCard($giftCardId, $order)
    {
        if (!$giftCardId) {
            $this->_logger->error(__('Gift card is not available for refund. Order id #%1', $order->getIncrementId()));
        }
        if ($this->checkConditionGiveCard($order)) {
            $giftCard = $this->_giftCardFactory->create()->load($giftCardId);
            $giftCard->setStatus(Status::STATUS_CANCELLED)
                ->setAction(Action::ACTION_REFUND)
                ->setActionVars(['order_increment_id' => $order->getIncrementId()])
                ->save();
        }
    }

}
