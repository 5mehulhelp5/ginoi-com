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
 * @category  Mageplaza
 * @package   Mageplaza_OrderHistory
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\View\LayoutFactory;
use Mageplaza\GiftCard\Model\Product\Type\GiftCard;
use Mageplaza\GiftCard\Helper\Data as HelperData;
use Mageplaza\GiftCard\Block\Adminhtml\Product\Edit\Tab\ReportColumn;
/**
 * Class OrderHistoryTab
 * @package Mageplaza\OrderHistory\Ui\DataProvider\Product\Form\Modifier
 */
class Report extends AbstractModifier
{
    const MPGFITCARD_REPORT = 'container_mpgiftcard_report';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var HelperData
     */
    protected $_helpData;

    /**
     * @var ArrayManager
     */
    protected  $arrayManager;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var
     */
    protected $layoutFactory;

    /**
     * OrderHistoryTab constructor.
     *
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param HelperData $helperData
     * @param ArrayManager $arrayManager
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        HelperData $helperData,
        ArrayManager $arrayManager,
        LayoutFactory $layoutFactory
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->_helpData = $helperData;
        $this->arrayManager = $arrayManager;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @param $meta
     * @return array
     */
    public function modifyMeta($meta)
    {
        if ($this->locator->getProduct()->getTypeId() !== GiftCard::TYPE_GIFTCARD
            || !$this->_helpData->getProductConfig('revenue_statistics', $this->_helpData->getStoreId())) {
            return $meta;
        }
        $this->meta = $meta;
        $this->addCustomTab();
        return $this->meta;
    }

    /**
     * @return void
     */
    protected function addCustomTab()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::MPGFITCARD_REPORT => $this->getTabReportListing(),
            ]
        );
    }


    /**
     * @return \array[][]
     */
    protected function getTabReportListing(){

        $productId = $this->locator->getProduct()->getId();
        return [
            'children' => [
                "mpgiftcard_report_coloumn" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "product_id" => $productId,
                                "required" => 0,
                                "sortOrder" => 5,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    ReportColumn::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ],
                'mpgiftcard_report_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'giftcard_report_listing',
                                'externalProvider' => 'giftcard_report_listing.giftcard_report_listing_data_source',
                                'selectionsProvider' => 'giftcard_report_listing.giftcard_report_listing.product_columns.ids',
                                'ns' => 'giftcard_report_listing',
                                'render_url' => $this->urlBuilder->getUrl(
                                    'mui/index/render',
                                    ['current_product_id' => $productId]
                                ),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'productId' => '${ $.provider }:data.product.current_product_id',
                                    '__disableTmpl' => ['productId' => false],
                                ],
                                'exports' => [
                                    'productId' => '${ $.externalProvider }:params.current_product_id',
                                    '__disableTmpl' => ['productId' => false],
                                ],
                                "sortOrder" => 10,
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Gift Card Report'),
                        'collapsible' => true,
                        'componentType' => Form\Fieldset::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.giftcard_report_listing',
                        'ns' => static::FORM_NAME,
                        'opened' => false,
                        'sortOrder' => '20'
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();

        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_product_id'] = $productId;

        return $data;
    }

    /**
     * @return ModuleManager|mixed
     */
    private function getModuleManager()
    {
        if ($this->moduleManager === null) {
            $this->moduleManager = ObjectManager::getInstance()->get(ModuleManager::class);
        }
        return $this->moduleManager;
    }
}
