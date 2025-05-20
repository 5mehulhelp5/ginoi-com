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

namespace Mageplaza\GiftCard\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayUtils;
use Mageplaza\GiftCard\Block\Product\View;
use Mageplaza\GiftCard\Helper\Product as GiftCardProductHelper;
use Mageplaza\GiftCard\Helper\SynChronized;
use Mageplaza\GiftCard\Model\TemplateFactory;


/**
 * Class ReportColumn
 * @package Mageplaza\GiftCard\Block\Adminhtml\Product\Edit\Tab
 */
class ReportColumn extends View
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/composite/column/report.phtml';

    /**
     * @var SynChronized
     */
    protected $synChronizedHelper;

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param TemplateFactory $templateFactory
     * @param GiftCardProductHelper $dataHelper
     * @param ProductHelper $productHelper
     * @param SynChronized $synChronizedHelper
     * @param array $data
     */
    public function __construct(
        Context                $context,
        ArrayUtils             $arrayUtils,
        TemplateFactory        $templateFactory,
        GiftCardProductHelper  $dataHelper,
        ProductHelper          $productHelper,
        SynChronized           $synChronizedHelper,

        array                  $data = [])
    {
        $this->_synChronizedHelper = $synChronizedHelper;

        parent::__construct($context, $arrayUtils, $templateFactory, $dataHelper, $productHelper, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return parent::getProduct();
    }

    /**
     * @param $attribute
     * @return float|int|mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductAttribute($attribute)
    {
        if($this->getProduct()->getId() !== null){
            $productId = $this->getProduct()->getId();
            $storeId = $this->getProduct()->getStoreId();
            $collection = $this->_synChronizedHelper->getCollectionReport();
            $totalArray = $this->_synChronizedHelper->getTotalGiftcard($productId);
            $this->_synChronizedHelper->updateTotalGiftCard($productId, $storeId, $collection);
        }
        $total = 0;
        switch ($attribute) {
            case SynChronized::ATR_TOTAL_CODE_REPORT:
                $total = $totalArray[SynChronized::ATR_TOTAL_CODE_REPORT] ?? 0;
                break;
            case SynChronized::ATR_TOTAL_BALANCE_REPORT:
                $total = $this->_synChronizedHelper->convertToBaseCurrency($totalArray[SynChronized::ATR_TOTAL_BALANCE_REPORT] ?? 0);
                break;
            case SynChronized::ATR_TOTAL_AMOUNT_REPORT:
                $total = $this->_synChronizedHelper->convertToBaseCurrency($totalArray[SynChronized::ATR_TOTAL_AMOUNT_REPORT] ?? 0);
                break;
            case SynChronized::ATR_TOTAL_PURCHASED_REPORT:
                $total = $this->_synChronizedHelper->convertToBaseCurrency($totalArray[SynChronized::ATR_TOTAL_PURCHASED_REPORT] ?? 0);
                break;
        }
        return $total;
    }


}
