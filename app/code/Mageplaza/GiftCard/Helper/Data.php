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
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\GiftCard\Model\Credit;
use Mageplaza\GiftCard\Model\GiftCard;
use Mageplaza\GiftCard\Model\GiftCard\Action;
use Mageplaza\GiftCard\Model\Source\GenerateGiftCodeEvent;


/**
 * Class Data
 * @package Mageplaza\GiftCard\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mpgiftcard';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var CustomerRegistry
     */
    protected $_customerRegistry;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected static $_jsonHelper;

    /**
     * @var Checkout Session
     */
    protected $_checkoutSession;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession
    ) {
        $this->_localeDate      = $localeDate;
        $this->_customerSession = $customerSession;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return $this->getGeneralConfig('enable', $storeId) && $this->isModuleOutputEnabled();
    }

    /**
     * @return PriceCurrencyInterface
     */
    public function getPriceCurrency()
    {
        if ($this->priceCurrency === null) {
            $this->priceCurrency = $this->objectManager->get(PriceCurrencyInterface::class);
        }

        return $this->priceCurrency;
    }

    /**
     * Get Customer object
     *
     * @param int $customerId
     *
     * @return Customer
     * @throws NoSuchEntityException
     */
    public function getCustomer($customerId = null)
    {
        $customer = null;
        if ($customerId instanceof Customer) {
            $customer = $customerId;
        } elseif ($customerId === null) {
            if ($this->_customerSession === null) {
                $this->_customerSession = $this->objectManager->get(CustomerSession::class);
            }
            if ($this->_customerSession->isLoggedIn()) {
                $customer = $this->_customerSession->getCustomer();
            }
        } elseif (is_numeric($customerId) && $customerId) {
            if ($this->_customerRegistry === null) {
                $this->_customerRegistry = $this->objectManager->get(CustomerRegistry::class);
            }
            $customer = $this->_customerRegistry->retrieve($customerId);
        }

        return $customer;
    }

    /**
     * Get Customer Credit Balance
     *
     * @param null $customerId
     * @param bool $convert
     * @param bool $format
     *
     * @return float|int|string
     * @throws NoSuchEntityException
     */
    public function getCustomerBalance($customerId = null, $convert = true, $format = false)
    {
        $customer = $this->getCustomer($customerId);
        if (!$customer || !$customer->getId()) {
            return 0;
        }

        $credit = $this->objectManager->create(Credit::class);
        $credit->load($customer->getId(), 'customer_id');

        $balance = $credit->getBalance() ?: 0;
        $balance = !$convert ? $balance : $this->convertPrice($balance, false);

        return $format ? $this->formatPrice($balance) : $balance;
    }

    /**
     * Get Gift card order
     *
     * @param GiftCard $giftCard
     *
     * @return Order|null
     */
    public function getGiftCardOrder($giftCard)
    {
        $orderId = $giftCard->getOrderIncrementId();
        if (!$orderId) {
            return null;
        }

        /** @var Order $order */
        $order = $this->objectManager->get(Order::class)
            ->loadByIncrementId($orderId);
        if (!$order || !$order->getId()) {
            return null;
        }

        return $order;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param      $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     * @param null $currency
     *
     * @return float|string
     */
    public function convertPrice($amount, $format = true, $includeContainer = true, $scope = null, $currency = null)
    {
        return $format
            ? $this->getPriceCurrency()->convertAndFormat(
                $amount,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope,
                $currency
            )
            : $this->getPriceCurrency()->convert($amount, $scope, $currency);
    }

    /**
     * @param      $amount
     * @param bool $includeContainer
     * @param null $scope
     * @param null $currency
     * @param int $precision
     *
     * @return float
     */
    public function formatPrice(
        $amount,
        $includeContainer = true,
        $scope = null,
        $currency = null,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->getPriceCurrency()->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * Get Time zone
     *
     * @param GiftCard $giftCard
     *
     * @return DateTimeZone
     */
    public function getGiftCardTimeZone($giftCard)
    {
        $timezone = $this->_localeDate->getConfigTimezone(ScopeInterface::SCOPE_STORE, $giftCard->getStoreId());

        return new DateTimeZone($timezone);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getStoreId()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            /** @var Store $store */
            if ($store = $this->storeManager->getWebsite($website)->getDefaultStore()) {
                $scope = $store->getId();
            }
        }

        return $scope;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getStoreCode()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getCode();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            /** @var Store $store */
            if ($store = $this->storeManager->getWebsite($website)->getDefaultStore()) {
                $scope = $store->getCode();
            }
        }

        return $scope;
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|DateTime|DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     *
     * @return string
     * @throws Exception
     */
    public function formatDate(
        $date = null,
        $format = IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof DateTimeInterface ? $date : new DateTime($date);

        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Get checkout session for admin and frontend
     *
     * @return Quote|Session
     */
    public function getCheckoutSession()
    {
        if (!$this->_checkoutSession) {
            $this->_checkoutSession = $this->objectManager->get($this->isAdmin() ? Quote::class : Session::class);
        }

        return $this->_checkoutSession;
    }

    /************************************** GENERAL CONFIGURATION ************************************************
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getGeneralConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('mpgiftcard/general' . $code, $storeId);
    }

    /**
     * Get gift code pattern default
     *
     * @param null $storeId
     *
     * @return string
     */
    public function getCodePattern($storeId = null)
    {
        return $this->getGeneralConfig('pattern', $storeId) ?: '[12AN]';
    }

    /**
     * Get gift code pattern default
     *
     * @param null $storeId
     *
     * @return string
     */
    public function getCountGiftCardUse($storeId = null)
    {
        return $this->getGeneralConfig('max_giftcard_use', $storeId) ?: '0';
    }

    /**
     * Get gift code pattern default
     *
     * @param null $storeId
     *
     * @return string
     */
    public function getIsCanRefund($storeId = null)
    {
        return $this->getGeneralConfig('is_can_refund', $storeId) ?: '0';
    }

    /**
     * Get allow gift message default
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAllowGiftMessage($storeId = null)
    {
        return $this->getGeneralConfig('allow_gift_message', $storeId);
    }

    /**
     * Get pool can inherit
     *
     * @param null $storeId
     *
     * @return string
     */
    public function getPoolCanInherit($storeId = null)
    {
        return $this->getGeneralConfig('pool_can_inherit', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function allowRedeemGiftCard($storeId = null)
    {
        $isEnableCredit = $this->getGeneralConfig('enable_credit', $storeId);
        $canRedeem      = $this->getGeneralConfig('can_redeem', $storeId);

        return $isEnableCredit && $canRedeem;
    }

    /************************************** PRODUCT CONFIGURATION ************************************************
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getProductConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('mpgiftcard/product' . $code, $storeId);
    }

    /**
     * Get expire after day default
     *
     * @param null $storeId
     *
     * @return string
     */
    public function getExpireAfterDay($storeId = null)
    {
        return $this->getProductConfig('expire_after_day', $storeId);
    }

    /**
     * @param string $when
     * @param null $storeId
     *
     * @return bool
     */
    public function isGenerateCode($when = GenerateGiftCodeEvent::ORDER_COMPLETED, $storeId = null)
    {
        return $this->getProductConfig('checkout/generate', $storeId) === $when;
    }

    /************************************** CHECKOUT CONFIGURATION ************************************************
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCheckoutConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('mpgiftcard/checkout' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isShowGiftcardImageProduct($storeId = null)
    {
        return (bool) $this->getCheckoutConfig('show_giftcard_image_product', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function canUsedForShipping($storeId = null)
    {
        return $this->getCheckoutConfig('process/used_for_shipping', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function canUsedForTax($storeId = null)
    {
        return $this->getCheckoutConfig('process/apply_for_tax', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function allowRefundGiftCard($storeId = null)
    {
        return $this->getCheckoutConfig('process/allow_refund', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isUsedCouponBox($storeId = null)
    {
        return $this->getCheckoutConfig('used_coupon_box', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isUsedMultipleCode($storeId = null)
    {
        $multiple = $this->getCheckoutConfig('used_multiple', $storeId);

        return $multiple && !$this->isUsedCouponBox($storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function canUsedCredit($storeId = null)
    {
        return $this->getCheckoutConfig('used_credit', $storeId);
    }

    /************************************** TEMPLATE CONFIGURATION *********************************************
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTemplateConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('mpgiftcard/template' . $code, $storeId);
    }

    /**
     * @return Template
     */
    public function getTemplateHelper()
    {
        return $this->objectManager->get(Template::class);
    }

    /**
     * @param null $storeId
     *
     * @return int
     */
    public function getMessageMaxChar($storeId = null)
    {
        return (int) $this->getTemplateConfig('message_max_char', $storeId) ?: 120;
    }

    /************************************** EMAIL CONFIGURATION ************************************************
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('mpgiftcard/email' . $code, $storeId);
    }

    /**
     * @return Email
     */
    public function getEmailHelper()
    {
        return $this->objectManager->get(Email::class);
    }

    /**
     * @param null $type
     * @param null $storeId
     *
     * @return bool
     */
    public function isEmailEnable($type = null, $storeId = null)
    {
        $typeEnable = true;
        if ($type) {
            $typeEnable = $this->getEmailConfig($type . '/enable');
        }

        return $typeEnable && $this->getEmailConfig('enable', $storeId);
    }

    /************************************** SMS CONFIGURATION ************************************************/
    /* @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSmsConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('mpgiftcard/sms' . $code, $storeId);
    }

    /**
     * @return Sms
     */
    public function getSmsHelper()
    {
        return $this->objectManager->get(Sms::class);
    }

    /**
     * @param null $type
     * @param null $storeId
     *
     * @return bool
     */
    public function isSmsEnable($type = null, $storeId = null)
    {
        $typeEnable = true;
        if ($type) {
            $typeEnable = $this->getSmsConfig($type . '/enable');
        }

        return $typeEnable && $this->getSmsConfig('enable', $storeId);
    }

    /**
     * @param array $rule
     *
     * @return string
     */
    public function convertConditions($rule)
    {
        $arr = [];

        foreach ($rule as $key => $value) {
            if ($key !== 'conditions' || !is_array($value)) {
                continue;
            }

            foreach ($value as $id => $valueData) {
                $node = &$arr;

                foreach (explode('--', $id) as $iValue) {
                    if (!isset($node[$key][$iValue])) {
                        $node[$key][$iValue] = [];
                    }

                    $node = &$node[$key][$iValue];
                }

                foreach ($valueData as $k => $v) {
                    $node[$k] = $v;
                }
            }
        }

        return self::jsonEncode($arr['conditions'][1]);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return mixed
     */
    public function getMaxWrongTimes($storeId = null)
    {
        return $this->getGeneralConfig('max_wrong_times', $storeId);
    }

    /**
     * @param GiftCard $giftCard
     * @param false $isCheckCode
     *
     * @throws LocalizedException
     */
    public function validateMaxWrongTime($giftCard, $isCheckCode = false)
    {
        $countKey         = $isCheckCode ? 'mp_giftcard_check_count' : 'mp_giftcard_add_count';
        $lastWrongTimeKey = $isCheckCode ? 'mp_giftcard_check_time' : 'mp_giftcard_add_time';

        $count    = $this->_customerSession->getData($countKey);
        $maxCount = $this->getMaxWrongTimes();

        if (!$giftCard->getId()) {
            $count++;
            $this->_customerSession->setData($countKey, $count);
            $this->_customerSession->setData($lastWrongTimeKey, time());

            throw new LocalizedException(__('The gift card code is not exits.'));
        }

        if (!$maxCount) {
            return;
        }
        $time          = 5;
        $lastWrongTime = $this->_customerSession->getData($lastWrongTimeKey);

        if ($count > $maxCount) {
            if (time() - $lastWrongTime > ($time * 60)) {
                $count = 0;
                $this->_customerSession->setData($countKey, $count);
                $this->_customerSession->setData($lastWrongTimeKey, 0);
            } else {
                throw new LocalizedException(__(
                    'You entered incorrectly more than %1 times. Please re-enter after %2 minutes',
                    $maxCount,
                    $time
                ));
            }
        }
    }

    /**
     * @param null|string|int $storeId
     *
     * @return mixed
     */
    public function getMaxFileSize($storeId = null)
    {
        return $this->getTemplateConfig('max_file_size', $storeId);
    }

    /**
     * @param $code
     * @param bool $storeId
     *
     * @return mixed
     */
    public function getGiveGiftCardConfig($code, $storeId = null)
    {
        return $this->getModuleConfig('product/checkout/' . $code, $storeId);
    }


    /**
     * @param GiftCard $giftCard
     *
     * @return void
     * @throws LocalizedException
     */
    public function checkMaxGiftCardUse($giftCard)
    {
        $countGiftCardUse = $giftCard->getCountGiftCardUse();
        if ($countGiftCardUse === 'used') {
            throw new LocalizedException(__(
                ' Gift Card "%1" cannot be redeemed because the gift card has expired or has exceeded the number of uses.',
                $giftCard->getCode()
            ));
        }
    }

    /**
     * @param GiftCard $giftCard
     * @param $action
     *
     * @return void
     */
    public function setValueMaxGiftCardUse($giftCard, $action = null)
    {
        $countGiftCardUse    = $giftCard->getCountGiftCardUse();
        $countGiftCardConfig = $this->getCountGiftCardUse($giftCard->getStoreId());

        if (empty($countGiftCardUse)) {
            $giftCard->setCountGiftCardUse($countGiftCardUse);
        } elseif ($countGiftCardUse == $countGiftCardConfig) {
            $giftCard->setCountGiftCardUse('used');
        }

        if ($countGiftCardUse < $countGiftCardConfig) {
            $giftCard->setCountGiftCardUse($countGiftCardUse + 1);
        }

        if ($countGiftCardUse === 'used' && $action == Action::ACTION_REFUND) {
            $giftCard->setCountGiftCardUse(1);
        }
    }

    /**
     * @param $contentFile
     *
     * @return bool
     */
    public static function fileIsPDF($contentFile)
    {
        $mineType = substr($contentFile, 0, 4);
        if ($mineType === '%PDF') {
            return true;
        }

        return false;
    }

    /**
     * @param $contentFile
     *
     * @return bool
     */
    public static function fileIsPNG($contentFile)
    {
        $mineType = substr($contentFile, 1, 3);
        if ($mineType === 'PNG') {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
       return trim($this->getConfigValue(self::CONFIG_MODULE_PATH.'/general/licence_key') ?: '');
    }

    /**
     * isHaveIndia by preg_match
     *
     * @param string $html
     *
     * @return bool
     */
    public static function isHaveIndia(string $html) {
        if (!$html) {
            return false;
        }
        $textContent = strip_tags($html);
        // Check for Devanagari script characters using regex
        $containsDevanagari = preg_match('/[\x{0900}-\x{097F}]/u', $textContent);
        if ($containsDevanagari) {
            return true;
        }

        return false;
    }
}
