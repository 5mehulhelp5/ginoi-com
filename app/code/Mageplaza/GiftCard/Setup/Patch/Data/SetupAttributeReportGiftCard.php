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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Validator\ValidateException;
use Mageplaza\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class InstallAttributeData
 * @package Mageplaza\SeoAnalysis\Setup\Patch\Data
 */

class SetupAttributeReportGiftCard implements DataPatchInterface, PatchRevertableInterface
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
     * @param CategorySetup $categorySetup
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
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;

        /**
         * Category attribute
         */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $catalogSetup->addAttribute(Product::ENTITY, 'mpgiftcard_total_code_report_gc', [
            'type'                    => 'varchar',
            'label'                   => 'Total Gift Card Code Report',
            'input'                   => 'text',
            'source'                  => '',
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => false,
            'required'                => false,
            'user_defined'            => true,
            'default'                 => '0',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => false,
            'unique'                  => false,
            'apply_to'                => GiftCard::TYPE_GIFTCARD,
            'note'                    => 'Total number of gift card codes created and used.'
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'mpgiftcard_total_balance_report_gc', [
            'type'                    => 'varchar',
            'label'                   => 'Total Balance Gift Card Code Report',
            'input'                   => 'text',
            'source'                  => '',
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => false,
            'required'                => false,
            'user_defined'            => true,
            'default'                 => '0',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => false,
            'unique'                  => false,
            'apply_to'                => GiftCard::TYPE_GIFTCARD,
            'note'                    => 'Total amount of gift card codes combined by product.'
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'mpgiftcard_total_amount_report_gc', [
            'type'                    => 'varchar',
            'label'                   => 'Total Amount Used Gift Card Code Report',
            'input'                   => 'text',
            'source'                  => '',
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => false,
            'required'                => false,
            'user_defined'            => true,
            'default'                 => '0',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => false,
            'unique'                  => false,
            'apply_to'                => GiftCard::TYPE_GIFTCARD,
            'note'                    => 'Total amount spent on gift cards.'
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'mpgiftcard_total_purchased_report_gc', [
            'type'                    => 'varchar',
            'label'                   => 'Total Amount Purchased Gift Card Code Report',
            'input'                   => 'text',
            'source'                  => '',
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => false,
            'required'                => false,
            'user_defined'            => true,
            'default'                 => '0',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => false,
            'unique'                  => false,
            'apply_to'                => GiftCard::TYPE_GIFTCARD,
            'note'                    => 'Total amount purchased with gift card products.'
        ]);
        $setup->endSetup();
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
        return '2.0.1';
    }
}
