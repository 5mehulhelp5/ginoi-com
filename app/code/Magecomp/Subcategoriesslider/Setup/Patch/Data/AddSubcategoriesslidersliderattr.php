<?php
namespace Magecomp\Subcategoriesslider\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class AddSubcategoriesslidersliderattr implements DataPatchInterface
{
    private $eavSetupFactory;
    private $setup;

    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $moduleDataSetup;
    }

    public function apply()
    {
        
         $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

           $eavSetup->addAttribute(
               \Magento\Catalog\Model\Category::ENTITY,
              'Subcategoriessliderslider',
            [
                'type' => 'text',
                'label' => 'Custom Subcatgory Slider',
                'input' => 'multiselect',
                'required' => false,
                'source' => 'Magecomp\Subcategoriesslider\Model\Category\Attribute\Source\Subcategoriessliderslider',
                'backend' => 'Magecomp\Subcategoriesslider\Model\Category\Attribute\Backend\Subcategoriessliderslider',
                'sort_order' => 20,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'ChooseSubCategory',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
           );
           $eavSetup->addAttribute(
               \Magento\Catalog\Model\Category::ENTITY,
              'Subcategoriessliderlist',
            [
                'type' => 'text',
                'label' => 'Custom Subcatgory list',
                'input' => 'multiselect',
                'required' => false,
                'source' => 'Magecomp\Subcategoriesslider\Model\Category\Attribute\Source\Subcategoriessliderlist',
                'backend' => 'Magecomp\Subcategoriesslider\Model\Category\Attribute\Backend\Subcategoriessliderlist',
                'sort_order' => 40,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'ChooseSubCategory',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
           );
           
        //$this->setup->endSetup();
    }
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}
