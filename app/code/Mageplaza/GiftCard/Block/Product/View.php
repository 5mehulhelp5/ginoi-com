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

namespace Mageplaza\GiftCard\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayUtils;
use Mageplaza\GiftCard\Helper\Data;
use Mageplaza\GiftCard\Helper\Product as GiftCardProductHelper;
use Mageplaza\GiftCard\Helper\Template;
use Mageplaza\GiftCard\Model\Source\Status;
use Mageplaza\GiftCard\Model\TemplateFactory;

/**
 * Class View
 * @package Mageplaza\GiftCard\Block\Product
 */
class View extends AbstractView
{
    /**
     * @var array
     */
    protected $_templates = [];

    /**
     * @var Template
     */
    protected $templateHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param TemplateFactory $templateFactory
     * @param GiftCardProductHelper $dataHelper
     * @param ProductHelper $productHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        TemplateFactory $templateFactory,
        GiftCardProductHelper $dataHelper,
        ProductHelper $productHelper,
        array $data = []
    ) {
        $this->templateFactory = $templateFactory;
        $this->dataHelper      = $dataHelper;
        $this->templateHelper  = $dataHelper->getTemplateHelper();
        $this->productHelper   = $productHelper;

        parent::__construct($context, $arrayUtils, $data);

        $this->_templates = $this->initTemplates();
    }

    /**
     * @return int
     */
    public function isUseTemplate()
    {
        return count($this->getTemplates());
    }

    /**
     * @return bool
     */
    public function isInstock()
    {
        $product = $this->getProduct();

        return (bool) $product->getQuantityAndStockStatus()['is_in_stock'];
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isEnabled()
    {
        $storeId = $this->dataHelper->getStoreId();

        return (bool) $this->dataHelper->isEnabled($storeId);
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->_templates;
    }

    /**
     * @return array
     */
    public function getProductConfig()
    {
        return [
            'information' => $this->prepareInformation(),
            'template'    => $this->_templates
        ];
    }

    /**
     * @return array
     */
    public function prepareInformation()
    {
        $product = $this->getProduct();

        return $this->dataHelper->getGiftCardProductInformation($product);
    }

    /**
     * @return array|void
     */
    public function initTemplates()
    {
        if (!$this->getProduct()) {
            return;
        }

        $resultTemplates = [];
        $templateIds     = $this->getProduct()->getGiftProductTemplate();
        if ($templateIds) {
            $templates = $this->templateFactory->create()
                ->getCollection()
                ->addFieldToFilter('template_id', ['in' => explode(',', $templateIds)])
                ->addFieldToFilter('status', Status::STATUS_ACTIVE);
            foreach ($templates->getItems() as $template) {
                $resultTemplates[$template->getId()] = $this->templateHelper->prepareTemplateData($template->getData());
                if (strtolower($this->getProduct()->getGiftCodePattern()) === "use_config") {
                    $giftcodePattern = $this->dataHelper->getCodePattern($this->getProduct()->getStoreId());
                } else {
                    $giftcodePattern = $this->getProduct()->getGiftCodePattern();
                }
                if ($giftCodePattern = $giftcodePattern) {
                    $resultTemplates[$template->getId()]['giftCodePattern'] = $giftCodePattern;
                }
            }
        }

        return $resultTemplates;
    }

    /**
     * @return array|mixed
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getConfigureData()
    {
        $configureData = [];
        /** @var Product $product */
        $product = $this->getProduct();
        if ($product->getConfigureMode() || $this->getRequest()->getParam('id')) {
            $configureData = $product->getPreconfiguredValues()->getData();
        }

        if (!isset($configureData['from']) && ($customer = $this->dataHelper->getCustomer())) {
            $configureData['from'] = $customer->getName();
        }

        return $configureData;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->addPageAsset('jquery/fileUploader/css/jquery.fileupload-ui.css');

        return parent::_prepareLayout();
    }

    /**
     * @return bool|string
     */
    public function getFonts()
    {
        $fonts = '';

        foreach ($this->_templates as $template) {
            if (!in_array($template['font'], ['Arial', 'times', 'helvetica', 'courier'])) {
                $fonts .= $template['font'] . '|';
            }
        }

        return substr($fonts, 0, -1);
    }

    /**
     * @return bool
     */
    public function getSkipSaleableCheck()
    {
        return $this->productHelper->getSkipSaleableCheck();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getStoreCode()
    {
        return $this->dataHelper->getStoreCode();
    }

    /**
     * @return mixed
     */
    public function isEnableCheckForm()
    {
        return $this->dataHelper->getProductConfig('check_form');
    }
}
