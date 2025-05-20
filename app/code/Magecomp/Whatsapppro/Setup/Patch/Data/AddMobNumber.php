<?php
namespace Magecomp\Whatsapppro\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMobNumber implements DataPatchInterface
{
    private $customerSetupFactory;
    protected $setup;

    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory, ModuleDataSetupInterface $setup)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
    }

    public function apply()
    {
        //$this->setup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);

            $customerSetup->addAttribute(
                Customer::ENTITY,
                'mobilenumber',
                [
                'type' => 'text',
                'label' => 'Mobile Number',
                'frontend_input' => 'text',
                'required' => false,
                'visible' => true,
                'system'=> 0,
                'position' => 80,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
                ]
            );

        // add attribute to form
        /** @var  $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobilenumber');
        $used_in_forms[]="adminhtml_customer";
        $used_in_forms[]="checkout_register";
        $used_in_forms[]="customer_account_create";
        $used_in_forms[]="customer_account_edit";
        $used_in_forms[]="adminhtml_checkout";

        $attribute->setData('used_in_forms', $used_in_forms);
        $attribute->save();
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
        return '1.0.4';
    }
}
