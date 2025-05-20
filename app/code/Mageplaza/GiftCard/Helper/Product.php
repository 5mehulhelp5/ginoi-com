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

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Config\Model\Config\Source\Locale\Timezone;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Model\CreditFactory;
use Mageplaza\GiftCard\Model\GiftCard\Action;
use Mageplaza\GiftCard\Model\GiftCard\Status;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\Product\DeliveryMethods;
use Mageplaza\GiftCard\Model\Source\FieldRenderer;
use Mageplaza\GiftCard\Model\Transaction;
use Mageplaza\GiftCard\Ui\DataProvider\Product\Modifier\GiftCard;

/**
 * Class Product
 * @package Mageplaza\GiftCard\Helper
 */
class Product extends Data
{
    /**
     * value use config
     */
    const VALUE_USE_CONFIG = 'use_config';

    /**
     * @var GiftCardFactory
     */
    protected $_giftCardFactory;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var LocaleFormat
     */
    private $localeFormat;

    /**
     * @var Timezone
     */
    private $timezoneSource;

    /**
     * @var DeliveryMethods
     */
    private $deliveryMethods;

    /**
     * @var CreditFactory
     */
    private $creditFactory;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param GiftCardFactory $giftCardFactory
     * @param Escaper $escaper
     * @param Renderer $renderer
     * @param LocaleFormat $localeFormat
     * @param Timezone $timezoneSource
     * @param DeliveryMethods $deliveryMethods
     * @param CreditFactory $creditFactory
     * @param Transaction $transaction
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        GiftCardFactory $giftCardFactory,
        Escaper $escaper,
        Renderer $renderer,
        LocaleFormat $localeFormat,
        Timezone $timezoneSource,
        DeliveryMethods $deliveryMethods,
        CreditFactory $creditFactory,
        Transaction $transaction
    ) {
        $this->_giftCardFactory = $giftCardFactory;
        $this->_escaper         = $escaper;
        $this->renderer         = $renderer;
        $this->localeFormat     = $localeFormat;
        $this->timezoneSource   = $timezoneSource;
        $this->deliveryMethods  = $deliveryMethods;
        $this->creditFactory    = $creditFactory;
        $this->transaction      = $transaction;

        parent::__construct($context, $objectManager, $storeManager, $localeDate, $customerSession);
    }

    /**
     * @param Order\Item $orderItem
     * @param null $qty
     *
     * @return $this
     */
    /**
     * @param Order $order
     * @param Order\Item $orderItem
     * @param null $qty
     *
     * @return $this
     * @throws Exception
     */
    public function generateGiftCode($order, $orderItem, $qty = null)
    {
        $options = $orderItem->getProductOptions();

        $giftCardIds = isset($options['giftcards']) ? $options['giftcards'] : [];

        if (count($giftCardIds) >= ($orderItem->getQtyOrdered() - $orderItem->getQtyRefunded())) {
            return $this;
        }

        if (!isset($options[FieldRenderer::AMOUNT]) || !$options[FieldRenderer::AMOUNT]) {
            $this->_logger->error(__(
                'Cannot create gift card from gift product. Item id #%1. Invalid amount.',
                $orderItem->getId()
            ));

            return $this;
        }

        $customerName = $order->getCustomerFirstname();
        if (!$customerName && $billing = $order->getBillingAddress()) {
            $customerName = $billing->getFirstname() . ' ' . $billing->getLastname();
        }
        $countGiftCardUse = 0;
        if(!empty($this->getCountGiftCardUse())){
            $countGiftCardUse = 1;
        }

        $giftCardData = [
            'pattern'               => $options['pattern'],
            'balance'               => $options[FieldRenderer::AMOUNT],
            'status'                => Status::STATUS_ACTIVE,
            'can_redeem'            => $options['can_redeem'],
            'store_id'              => $order->getStoreId(),
            'expire_after'          => $options['expire_after'],
            'template_id'           => $options[FieldRenderer::TEMPLATE] ?? '',
            'image'                 => isset($options[FieldRenderer::IMAGE]) ? $options[FieldRenderer::IMAGE] : '',
            'template_fields'       => [
                'sender'    => $options[FieldRenderer::SENDER] ?? $customerName,
                'recipient' => isset($options[FieldRenderer::RECIPIENT]) ? $options[FieldRenderer::RECIPIENT] : '',
                'message'   => isset($options[FieldRenderer::MESSAGE]) ? $options[FieldRenderer::MESSAGE] : ''
            ],
            'order_item_id'         => $orderItem->getId(),
            'order_increment_id'    => $order->getIncrementId(),
            'delivery_method'       => $options[FieldRenderer::METHOD],
            'action_vars'           => [
                'auth'               => $customerName,
                'order_increment_id' => $order->getIncrementId()
            ],
            'conditions_serialized' => $this->getProdAttrVal($orderItem->getProduct(), GiftCard::FIELD_CONDITIONS),
            'is_can_refund'         => $this->getIsCanRefund($order->getStoreId()),
            'product_id'            => $orderItem->getProduct()->getId(),
            'row_total'             => ($orderItem->getRowTotal() - $orderItem->getDiscountAmount() + $orderItem->getTaxAmount()) / $orderItem->getQtyOrdered(),
            'count_giftcard_use'    => $countGiftCardUse,
            'is_config_refund'      => 1

        ];

        switch ($options[FieldRenderer::METHOD]) {
            case DeliveryMethods::METHOD_PRINT:
                $deliveryAddress = $order->getCustomerEmail();
                if (!$order->getCustomerIsGuest()) {
                    $giftCardData['customer_ids'] = $order->getCustomerId();
                }
                break;
            case DeliveryMethods::METHOD_POST:
                $deliveryAddress = $this->renderer->format($order->getShippingAddress(), 'oneline');
                break;
            default:
                $deliveryAddress = isset($options[FieldRenderer::ADDRESS]) ? $options[FieldRenderer::ADDRESS] : '';
                break;
        }
        $giftCardData['delivery_address'] = $deliveryAddress;

        $timezone = null;
        if (isset($options[FieldRenderer::TIMEZONE])) {
            $giftCardData['timezone'] = $options[FieldRenderer::TIMEZONE];
            $timezone                 = new DateTimeZone($options[FieldRenderer::TIMEZONE]);
        }

        if (isset($options[FieldRenderer::DATE])) {
            $giftCardData['delivery_date'] = $options[FieldRenderer::DATE];
        } elseif ((int) $options[FieldRenderer::METHOD] !== DeliveryMethods::METHOD_POST) {
            $giftCardData['delivery_date']     = (new DateTime('', $timezone))->format('Y-m-d');
            $giftCardData['send_to_recipient'] = true;
        }

        if (isset($options['expire_after']) && $options['expire_after'] > 0) {
            $giftCardData['expired_at'] = (new DateTime(
                '+' . $options['expire_after'] . ' day',
                $timezone
            ))->format('Y-m-d');
        }

        $availableQty = $orderItem->getQtyOrdered() - $orderItem->getQtyRefunded() - count($giftCardIds);
        $qty          = $qty === null ? $availableQty : min($qty, $availableQty);

        while ($qty--) {
            try {
                $giftCard      = $this->_giftCardFactory->create()->addData($giftCardData)->save();
                $giftCardIds[] = $giftCard->getId();
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }

        $options['giftcards'] = $giftCardIds;
        $orderItem->setProductOptions($options);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attribute
     *
     * @return array|bool|string
     */
    public function getProdAttrVal($product, $attribute)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $product->getResource();

        return $resource->getAttributeRawValue($product->getId(), $attribute, $product->getStoreId());
    }

    /**
     * @param Order\Item $orderItem
     * @param float $qty
     *
     * @return $this
     * @throws Exception
     */
    public function refundGiftCode($orderItem, $qty)
    {
        if (!$qty) {
            return $this;
        }

        $options = $orderItem->getProductOptions();

        $RefundableGiftCardIds = isset($options['refundable_gift_card']) ? $options['refundable_gift_card'] : [];
        $giftCardIds           = isset($options['giftcards']) ? $options['giftcards'] : [];
        if (!($countGiftCard = count($RefundableGiftCardIds))) {
            $this->_logger->error(__('Gift card is not available for refund. Item id #%1', $orderItem->getId()));

            return $this;
        }
        $qty = min($qty, $countGiftCard);
        while ($qty--) {
            $id       = array_shift($RefundableGiftCardIds);
            $giftCard = $this->_giftCardFactory->create()->load($id);
            if (!$giftCard->getId()) {
                continue;
            }

            try {
                $giftCard->setStatus(Status::STATUS_CANCELLED)
                    ->setAction(Action::ACTION_REFUND)
                    ->setActionVars(['order_increment_id' => $orderItem->getOrder()->getIncrementId()])
                    ->save();
                $giftCardIds = array_diff($giftCardIds, [$id]);
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }

        $options['giftcards'] = $giftCardIds;
        $orderItem->setProductOptions($options)->save();

        return $this;
    }

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function getGiftCardCodesFromIds($ids = [])
    {
        $giftCard = $this->_giftCardFactory->create();

        $giftCardCodes = $giftCard->getCollection()
            ->addFieldToFilter('giftcard_id', ['in' => $ids])
            ->getColumnValues('code');

        if (!$this->isAdmin()) {
            foreach ($giftCardCodes as $key => $code) {
                $giftCardCodes[$key] = $giftCard->getHiddenCode($code);
            }
        }

        return $giftCardCodes;
    }

    /**
     * @param $optionCode
     * @param $item
     *
     * @return mixed|string
     */
    protected function getOptionValue($optionCode, $item)
    {
        if ($item instanceof Item) {
            $option = $item->getOptionByCode($optionCode);
            if ($option) {
                return $option->getValue();
            }
        } else {
            $option = $item->getProductOptionByCode($optionCode);
            if ($option) {
                return $option;
            }
        }

        return false;
    }

    /**
     * @param Order\Item $item
     * @param array $options
     * @param null $scope
     *
     * @return array
     */
    public function getOptionList($item, $options = [], $scope = null)
    {
        $optionList = [];
        $fieldLists = FieldRenderer::getOptionArray();
        $optionShow = explode(',', $this->getProductConfig('checkout/item_renderer'));
        foreach ($optionShow as $option) {
            $value = $this->getOptionValue($option, $item);
            if (!$value) {
                continue;
            }
            switch ($option) {
                case FieldRenderer::AMOUNT:
                    $value = $this->convertPrice($value, true, false, $scope);
                    break;
                case FieldRenderer::METHOD:
                    $methodOptions = DeliveryMethods::getMethodOptionArray();
                    $value         = $methodOptions[$value];
                    break;
                case FieldRenderer::TEMPLATE:
                    $template = $this->getTemplateHelper()->getTemplateById($value);
                    if ($template && $template->getId()) {
                        $value = $template->getName();
                    }
                    break;
                case FieldRenderer::MESSAGE:
                case FieldRenderer::SENDER:
                case FieldRenderer::RECIPIENT:
                    $value = $this->_escaper->escapeHtml($value);
                    break;
            }

            $optionList[] = ['label' => $fieldLists[$option], 'value' => $value, 'custom_view' => false];
        }

        return array_merge($optionList, $options);
    }

    /**
     * @param $product
     *
     * @return array
     */
    public function getGiftCardProductInformation($product)
    {
        $deliveryParam = $product->getConfigureMode() || $this->_getRequest()->getParam('id')
            ? $product->getPreconfiguredValues()->getData()
            : [];

        $enableDeliveryDate = (int) $product->getGiftCardType() !== DeliveryMethods::TYPE_PRINT
            && $this->getProductConfig('enable_delivery_date');

        $expiredDay = $product->getExpireAfterDay();
        if ($expiredDay === self::VALUE_USE_CONFIG) {
            $expiredDay = $this->getProductConfig('expire_after_day');
        }
        if (strtolower($product->getGiftCodePattern() ?? '') === "use_config") {
            $giftcodePattern = $this->getCodePattern($product->getStoreId());
        } else {
            $giftcodePattern = $product->getGiftCodePattern();
        }
        $information = [
            'productId'          => $product->getId(),
            'giftcodePattern'    => $giftcodePattern,
            'currencyRate'       => $this->getPriceCurrency()->convert(1),
            'priceFormat'        => $this->localeFormat->getPriceFormat(),
            'amounts'            => $product->getGiftCardAmounts() ?: [],
            'delivery'           => $this->deliveryMethods->getDeliveryMethod(
                $product->getGiftCardType(),
                $deliveryParam
            ),
            'enableDeliveryDate' => $enableDeliveryDate,
            'timezone'           => [
                'enable'  => $enableDeliveryDate && $this->getProductConfig('enable_timezone'),
                'options' => $this->timezoneSource->toOptionArray(),
                'value'   => $this->getConfigValue('general/locale/timezone')
            ],
            'fileUploadUrl'      => $this->_urlBuilder->getUrl('mpgiftcard/template/upload'),
            'messageMaxChar'     => $this->getMessageMaxChar(),
            'uploadTooltip'      => __(
                'Acceptable formats are jpg, png and gif. Limit Image Size Upload (%1MB)',
                $this->getMaxFileSize()
            ),
            'expire_after'       => $expiredDay
        ];

        if ((boolean) $product->getAllowAmountRange()) {
            $minAmount = $product->getMinAmount();
            $minAmount = (!$minAmount || $minAmount < 0) ? 0 : $minAmount;

            $maxAmount = $product->getMaxAmount();
            $maxAmount = ($maxAmount && $maxAmount < $minAmount) ? $minAmount : $maxAmount;

            $priceRate = $product->getPriceRate() ?: 100;

            $information['openAmount'] = [
                'min'  => $minAmount,
                'max'  => $maxAmount,
                'rate' => $priceRate,
            ];
        }

        return $information;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     * @throws Exception
     */
    public function getDashboardConfig($customer)
    {
        if (!$customer || !$customer->getId()) {
            return [];
        }

        $emailEnable       = $this->getEmailConfig('enable');
        $creditEmailEnable = $this->getEmailConfig('credit/enable');

        $creditAccount = $this->creditFactory->create()
            ->load($customer->getId(), 'customer_id');
        $code          = $this->_getRequest()->getParam('code');

        return [
            'baseUrl'        => $this->_urlBuilder->getBaseUrl(),
            'customerEmail'  => $customer->getEmail(),
            'code'           => $code,
            'balance'        => $this->getCustomerBalance($customer->getId()),
            'transactions'   => $this->transaction->getTransactionsForCustomer($customer->getId()),
            'giftCardLists'  => $this->_giftCardFactory->create()->getGiftCardListForCustomer($customer->getId()),
            'isEnableCredit' => (bool) $this->getGeneralConfig('enable_credit'),
            'notification'   => [
                'enable'               => $emailEnable,
                'creditEnable'         => $creditEmailEnable,
                'creditNotification'   => $creditAccount->getCreditNotification() === null || (boolean) $creditAccount->getCreditNotification(),
                'giftcardNotification' => $creditAccount->getGiftcardNotification() === null || (boolean) $creditAccount->getGiftcardNotification()
            ]
        ];
    }
}
