<?php

namespace Mageplaza\GiftCard\Setup\Patch\Data;
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
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Mageplaza\GiftCard\Model\Attribute\Backend\MultiSelect;
use Mageplaza\GiftCard\Model\Config\Source\CustomerGroups;
use Mageplaza\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class InstallAttributeData
 * @package Mageplaza\SeoAnalysis\Setup\Patch\Data
 */

class SetupAttributeTax implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetup
     */
    private $categorySetup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetup $categorySetup
     * @param CategorySetupFactory $categorySetupFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetup $categorySetup,
        CategorySetupFactory $categorySetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetup = $categorySetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;

        /**
         * Category attribute
         */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $this->setTaxClassForAllProducts();

        $catalogSetup->addAttribute(Product::ENTITY, 'mpgiftcard_customergroup', [
            'group'                   => 'Product Details',
            'type'                    => 'text',
            'label'                   => 'Applies to Customer Groups',
            'input'                   => 'multiselect',
            'class'                   => 'mpgiftcard_customergroup',
            'source'                  => CustomerGroups::class,
            'backend'                 => MultiSelect::class,
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'default'                 => '0,1,2,3',
            'visible'                 => true,
            'is_required'             => true,
            'required'                => true,
            'user_defined'            => false,
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => true,
            'unique'                  => false,
            'apply_to'                => 'mpgiftcard',
            'note'                    => 'Customers belonging to the selected customer group will be able to purchase this gift card product.'
        ]);

        $setup->endSetup();
    }

    /**
     * Set tax class for all products
     */
    private function setTaxClassForAllProducts()
    {
        $fieldAdd = ['tax_class_id'];
        foreach ($fieldAdd as $field) {
            $applyTo = $this->categorySetup->getAttribute('catalog_product', $field, 'apply_to');
            if ($applyTo) {
                $applyTo = explode(',', $applyTo);
                if (!in_array(GiftCard::TYPE_GIFTCARD, $applyTo, true)) {
                    $applyTo[] = GiftCard::TYPE_GIFTCARD;
                    $this->categorySetup->updateAttribute('catalog_product', 'tax_class_id', 'apply_to', implode(',', $applyTo));
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            'group'                   => 'Gift Card Information',
            'backend'                 => '',
            'frontend'                => '',
            'class'                   => '',
            'source'                  => '',
            'global'                  => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible'                 => true,
            'required'                => false,
            'user_defined'            => true,
            'default'                 => '',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'unique'                  => false,
            'apply_to'                => GiftCard::TYPE_GIFTCARD,
            'used_in_product_listing' => true
        ];
    }


    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
