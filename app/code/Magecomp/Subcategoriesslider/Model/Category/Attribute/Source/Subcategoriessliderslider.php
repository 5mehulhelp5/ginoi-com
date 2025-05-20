<?php

namespace Magecomp\Subcategoriesslider\Model\Category\Attribute\Source;

use Magento\Framework\Registry;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;

class Subcategoriessliderslider extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_catalogConfig;
    protected $_registry;
    protected $_categoryCollectionFactory;
    protected $_messageManager;

    public function __construct(
        Config $catalogConfig,
        Registry $registry,
        CollectionFactory $categoryCollectionFactory,
        ManagerInterface $messageManager
    ) {
        $this->_catalogConfig = $catalogConfig;
        $this->_registry = $registry;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_messageManager = $messageManager;
    }

    protected function _getCatalogConfig()
    {
        return $this->_catalogConfig;
    }

    public function getAllOptions()
    {

        if ($this->_options === null) {
            $category = $this->_registry->registry('current_category'); // Get Current Category

            if (!$category) {
                return [
                    ['label' => __('No Current Category'), 'value' => '']
                ];
            }


            $newCategoryName = $category->getName();
            $newCategoryName = str_replace(' ', '', (string)$newCategoryName);

            $categoryCollection = $this->_categoryCollectionFactory->create();
            $categoryCollection->addAttributeToSelect('name');

            $duplicateFound = false;
            foreach ($categoryCollection as $existingCategory) {
                $existingCategoryName = $existingCategory->getName();
                $existingCategoryName = str_replace(' ', '', (string)$existingCategoryName);
                
                if ($existingCategoryName !== null && strcasecmp($existingCategoryName, (string)$newCategoryName) == 0 && $existingCategory->getId() != $category->getId()) {
                    $duplicateFound = true;
                    break;
                }
            }

            if ($duplicateFound) {
              
                return [
                    ['label' => __('A category with the same name already exists.'), 'value' => '']
                ];
            }

            $subcats = $category->getChildrenCategories();
            if (!$subcats->count()) {
                return [
                    ['label' => __('No Subcategory'), 'value' => 'No Subcategory']
                ];
            }

            $this->_options = [];
            foreach ($subcats as $subcat) {
                $this->_options[] = [
                    'label' => __($subcat->getName()),
                    'value' => $subcat->getId()
                ];
            }

            if (empty($this->_options)) {
                $this->_options = [
                    ['label' => __('No Subcategory'), 'value' => 'No Subcategory']
                ];
            }
        }
        return $this->_options;
    }
}
