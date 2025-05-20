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
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Model\GiftCard;
use Mageplaza\GiftCard\Model\ResourceModel\Report\Grid\Collection;
use Mageplaza\GiftCard\Model\ResourceModel\Report\Grid\Collection as ReportInterceptor;
use Mageplaza\GiftCard\Model\Product\Type\GiftCard as Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Sales\Model\Order;

/**
 * Class Sms
 * @package Mageplaza\GiftCard\Helper
 */
class SynChronized extends Data
{
    const ATR_TOTAL_CODE_REPORT      = 'mpgiftcard_total_code_report_gc';
    const ATR_TOTAL_BALANCE_REPORT   = 'mpgiftcard_total_balance_report_gc';
    const ATR_TOTAL_AMOUNT_REPORT    = 'mpgiftcard_total_amount_report_gc';
    const ATR_TOTAL_PURCHASED_REPORT = 'mpgiftcard_total_purchased_report_gc';

    /**
     * @var ProductAction
     */
    protected $_productAction;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ReportInterceptor
     */
    protected $reportInterceptor;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var GiftCardFactory
     */
    protected $giftCardFactory;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param ReportInterceptor $reportInterceptor
     * @param ProductCollectionFactory $productCollection
     * @param CurrencyFactory $currencyFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductAction $productAction
     * @param ItemFactory $itemFactory
     * @param GiftCardFactory $giftCardFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        ReportInterceptor $reportInterceptor,
        ProductCollectionFactory $productCollection,
        CurrencyFactory $currencyFactory,
        PriceCurrencyInterface $priceCurrency,
        ProductAction $productAction,
        ItemFactory $itemFactory,
        GiftCardFactory $giftCardFactory
    ) {
        $this->reportInterceptor         = $reportInterceptor;
        $this->_productCollectionFactory = $productCollection;
        $this->_productAction            = $productAction;
        $this->currencyFactory           = $currencyFactory;
        $this->priceCurrency             = $priceCurrency;
        $this->itemFactory               = $itemFactory;
        $this->giftCardFactory           = $giftCardFactory;

        parent::__construct($context, $objectManager, $storeManager, $localeDate, $customerSession);
    }

    /**
     * @return array
     */
    public function getProductIdGiftCard()
    {
        return $this->_productCollectionFactory->create()
            ->addAttributeToFilter('type_id', ['eq' => Type::TYPE_GIFTCARD])
            ->addAttributeToSelect('*')->getAllIds();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function synchronizedGiftCardProduct()
    {
        $productId  = $this->getProductIdGiftCard();
        $collection = $this->getCollectionReport();
        $storeId    = $this->getStoreId();
        foreach ($productId as $id) {
            $this->updateTotalGiftCard($id, $storeId, $collection);
        }

        return $productId;
    }

    /**
     * @return ReportInterceptor
     */
    public function getCollectionReport()
    {
        $collection = $this->reportInterceptor;
        $collection->addFieldToFilter('code', ['notnull' => true]);

        return $collection;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function synchronizedCodeProduct()
    {
        $collection = $this->reportInterceptor;
        $collection->addFieldToFilter('code', ['notnull' => true]);
        $collection->addFieldToFilter('order_item_id', ['notnull' => true])->addFieldToFilter('order_item_id',
            ['gt' => 0]);
        $collection->addFieldToFilter('product_id', ['null' => true]);
        $collection->addFieldToFilter('row_total', ['null' => true]);

        $giftCardModel = $this->giftCardFactory->create();

        /** @var GiftCard $giftCard */
        foreach ($collection as $giftCard) {
            $itemOrder = $this->getItemOder($giftCard->getOrderItemId());

            if ($giftCard instanceof GiftCard && $giftCard->getId()) {
                $giftCardModel->load($giftCard['giftcard_id']);
                $giftCardModel->setData('product_id', $itemOrder['product_id']);
                $giftCardModel->setData('row_total', $itemOrder['row_total']);
                $giftCardModel->setAction(false);
                $giftCardModel->save();
            }

        }
    }

    /**
     * @param $productId
     * @param int $storeId
     * @param $collection
     *
     * @return SynChronized
     */
    public function updateTotalGiftCard($productId, $storeId, $collection)
    {
        /** @var Collection $collection */
        $collection->getSelect()->reset('where');
        $collection->clear();
        $collection->addFieldToFilter(
            'main_table.product_id',
            $productId
        );
        $this->_productAction->updateAttributes(
            [$productId],
            [self::ATR_TOTAL_CODE_REPORT => count($collection)],
            $storeId
        );
        $this->_productAction->updateAttributes(
            [$productId],
            [self::ATR_TOTAL_BALANCE_REPORT => array_sum($collection->getColumnValues('balance'))],
            $storeId
        );
        $this->_productAction->updateAttributes(
            [$productId],
            [self::ATR_TOTAL_AMOUNT_REPORT => array_sum($collection->getColumnValues('amount_used'))],
            $storeId
        );
        $this->_productAction->updateAttributes(
            [$productId],
            [self::ATR_TOTAL_PURCHASED_REPORT => array_sum($collection->getColumnValues('row_total'))],
            $storeId
        );

        return $this;
    }

    /**
     * @param $productId
     *
     * @return DataObject|null
     */
    public function getProductById($productId)
    {
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToFilter('entity_id', $productId);
        $productCollection->setPageSize(1)->addAttributeToSelect('*');
        $product = $productCollection->getFirstItem();

        if ($product->getId()) {
            return $product;
        } else {
            return null;
        }
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getTotalGiftcard($productId)
    {
        $product   = $this->getProductById($productId);
        $dataTotal = [];
        if (!isset($product)) {
            return $dataTotal;
        }
        $totalCodeValue = $product->getData(self::ATR_TOTAL_CODE_REPORT);
        if ($totalCodeValue !== null) {
            $dataTotal[self::ATR_TOTAL_CODE_REPORT] = $totalCodeValue;
        }

        $totalBalance = $product->getData(self::ATR_TOTAL_BALANCE_REPORT);
        if ($totalBalance !== null) {
            $dataTotal[self::ATR_TOTAL_BALANCE_REPORT] = $totalBalance;
        }

        $totalAmountUsed = $product->getData(self::ATR_TOTAL_AMOUNT_REPORT);
        if ($totalAmountUsed !== null) {
            $dataTotal[self::ATR_TOTAL_AMOUNT_REPORT] = $totalAmountUsed;
        }

        $totalPurchased = $product->getData(self::ATR_TOTAL_PURCHASED_REPORT);
        if ($totalPurchased !== null) {
            $dataTotal[self::ATR_TOTAL_PURCHASED_REPORT] = $totalPurchased;
        }

        return $dataTotal;

    }

    /**
     * @param $price
     *
     * @return float|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertToBaseCurrency($price)
    {
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        $baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();

        $rate = $this->currencyFactory->create()->load($currentCurrency)->getAnyRate($baseCurrency);

        return $this->priceCurrency->format($price * $rate, false, 2, null, $baseCurrency);
    }

    /**
     * @param $itemID
     *
     * @return int[]
     */
    public function getItemOder($itemID)
    {

        $itemGiftCard = [
            'product_id' => 0,
            'row_total'  => 0
        ];
        $orderItem    = $this->itemFactory->create()->getCollection()->addFieldToFilter('item_id', $itemID);

        /** @var Order\Item $item */
        foreach ($orderItem as $item) {
            $itemGiftCard['row_total']  += $item->getQtyOrdered() > 1 ? $item->getOriginalPrice() : $item->getRowTotal();
            $itemGiftCard['product_id'] = $item->getProduct()->getId() ?? null;
        }


        return $itemGiftCard;
    }
}
