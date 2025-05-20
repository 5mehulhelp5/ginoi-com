<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config;

class MassUpdateProducts extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    public $_prodAttrCollection;
    public $_helperData;

    public $_scopeConfig;


    public $categoryLinkManagement;
    public $productRepository;
    public $_storeManager;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $prodAttrCollection,
        \Magento\Framework\App\Config $config,
        \Iksanika\Productmanage\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagementInterface,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        parent::__construct($context, $productBuilder);
        $this->_prodAttrCollection = $prodAttrCollection;
        $this->_helperData = $helper;
//        $this->_helperData->setScopeConfig($context->getScopeConfig());
        $this->_helperData->setScopeConfig($config);
        $this->_scopeConfig = $config;


        $this->categoryLinkManagement = $categoryLinkManagementInterface;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        /*
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create('Magento\Catalog\Model\Product')->isProductsHasSku($productIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
\         */
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        $storeId = $this->getRequest()->getParam('store', 0);
        $store = $this->_storeManager->getStore($storeId);
        $this->_storeManager->setCurrentStore($store->getCode());

        $productIds = (array) $this->getRequest()->getParam('product');
        
        if(is_array($productIds))
        {
            $productFactory = new \Magento\Catalog\Model\ProductFactory($this->_objectManager);
            $attributes = $this->_prodAttrCollection
                            ->addVisibleFilter()
//                                ->addStoreLabel($this->_helperData->getStoreId());
                            ->addStoreLabel($storeId);
            $attributes->getSelect();

            $attrs    =   array();
            foreach($attributes as $attribute)
            {
                $attrs[$attribute->getAttributeCode()]  =   $attribute;
            }

            //$columnForUpdate        =   \Iksanika\Productmanage\Block\Catalog\Product\Grid::getColumnForUpdate();
            $columnForUpdate        =   $this->_helperData->getColumnForUpdate();
            $columnForUpdateFlip    =   array_flip($columnForUpdate);

            if(isset($columnForUpdateFlip['is_in_stock']) || isset($columnForUpdateFlip['qty']))
            {
                // TODO why use ObjectManager?
               // @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
                $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                // @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
                $stockItemRepository = $this->_objectManager->create('Magento\CatalogInventory\Api\StockItemRepositoryInterface');
            }
            $isUpdated = 0;
            foreach($productIds as $itemId => $productId)
            {
                try {
                    $product = $this->productRepository->getById($productId, true, $storeId);

                    $isInventoryValues = false;

                    if($product->getId())
                    {
                        $isCategoriesUpdated = false;


                        try
                        {
                            $productBefore  =   $product;
                            $stockData      =   null;
                            
                            if(isset($columnForUpdateFlip['is_in_stock']) || isset($columnForUpdateFlip['qty']))
                            {
                                $isInventoryValues = true;
                                //if(!$stockData)
                                $stockData = $product->getStockItem();
                                $stockData['product_id'] = $product->getId();
                                $stockItemDo = $stockRegistry->getStockItem(
                                    $productId
                                    //,
                                    //$this->attributeHelper->getStoreWebsiteId($storeId)
                                );
                           }
                           
                        } catch (\Exception $e) {
                            $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                            $this->logger->critical($e);
                        }
                    }

                    $i = 0;
                    foreach($columnForUpdate as $columnName)
                    {
                        $columnValuesForUpdate = $this->getRequest()->getParam($columnName);
                        // handle exceptional situation or related tables savings
                        if($columnName == 'is_in_stock')
                        {
                            if(!$this->_scopeConfig->getValue('iksanika_productmanage/stockmanage/autoStockStatus'))
                            {
                                $stockItemDo->setData('is_in_stock', (int) $columnValuesForUpdate[$itemId]);
                            }
                        }else
                        if($columnName == 'qty')
                        {
                            $columnValuesForUpdate[$itemId] = str_replace(' ', '', $columnValuesForUpdate[$itemId]);
                            $isEmpty = ($columnValuesForUpdate[$itemId] == '') ? true : false;
                            if(!$isEmpty)
                            {
                                if ($columnValuesForUpdate[$itemId][0] == '-')
                                    $qty = (float) ($stockItemDo->getData('qty')) - (float) (substr($columnValuesForUpdate[$itemId], 1, strlen($columnValuesForUpdate[$itemId])));
                                elseif ($columnValuesForUpdate[$itemId][0] == '+')
                                    $qty = (float) ($stockItemDo->getData('qty')) + (float) (substr($columnValuesForUpdate[$itemId], 1, strlen($columnValuesForUpdate[$itemId])));
                                elseif ($columnValuesForUpdate[$itemId][0] == '*')
                                    $qty = (float) ($stockItemDo->getData('qty')) * (float) (substr($columnValuesForUpdate[$itemId], 1, strlen($columnValuesForUpdate[$itemId])));
                                else
                                    $qty = $columnValuesForUpdate[$itemId];

                                $stockItemDo->setData('qty', $qty);
                                if($this->_scopeConfig->getValue('iksanika_productmanage/stockmanage/autoStockStatus'))
                                {
                                    $stockItemDo->setData('is_in_stock', (string) (($qty > 0) ? 1 : 0));
                                }
                            }else
                            {
                                $stockItemDo->setData('qty', '');
                            }

                        }else
                        if($columnName == 'category_ids')
                        {
                            $isCategoriesUpdated = true;

                            try {
                                $categoryIds = explode(',', $columnValuesForUpdate[$itemId] ?? '');

                                // new functionality instead of depricated one
                                $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
                                $verifiedCategories = $category->verifyIds($categoryIds);

                                $categoryIds = array();
                                foreach ($verifiedCategories as $k => $categoryId) {
                                    $categoryIds[] = (int)$categoryId;
                                }
                                /*
                                 * it has been moved after $product->save block/section due to conflict of overrides of links (old over new links)
                                 *
                                // save products to categories links
                                $this->categoryLinkManagement->assignProductToCategories(
                                    $product->getSku(),
                                    $categoryIds
                                );

                                // depricated is not working on M2.4.x
                                $product->setCategoryIds($categoryIds);
                                */
                            } catch (\Exception $e) {
                                $this->messageManager->addErrorMessage($e->getMessage());
                            }

                        }else
                        if($columnName == 'related_ids')
                        {
                            $relatedIds = trim($columnValuesForUpdate[$itemId] ?? '') != "" ? explode(',', str_replace(' ','',trim($columnValuesForUpdate[$itemId] ?? '')) ?? '') : [];

                            $product->setProductLinks($this->_helperData->removeProductLinksByType($product, 'related'));
                            $product->setProductLinks($this->_helperData->addProductsLinksByType($product, $relatedIds, 'related'));
                        }else
                        if($columnName == 'cross_sell_ids')
                        {
                            $crossSellIds = trim($columnValuesForUpdate[$itemId] ?? '') != "" ? explode(',', str_replace(' ','',trim($columnValuesForUpdate[$itemId] ?? ''))) : [];

                            // update cross sell
                            $product->setProductLinks($this->_helperData->removeProductLinksByType($product, 'crosssell'));
                            $product->setProductLinks($this->_helperData->addProductsLinksByType($product, $crossSellIds, 'crosssell'));
                        }else
                        if($columnName == 'up_sell_ids')
                        {
                            $upSellIds = trim($columnValuesForUpdate[$itemId] ?? '') != "" ? explode(',', str_replace(' ','',trim($columnValuesForUpdate[$itemId] ?? ''))) : [];

                            // update up sell
                            $product->setProductLinks($this->_helperData->removeProductLinksByType($product, 'upsell'));
                            $product->setProductLinks($this->_helperData->addProductsLinksByType($product, $upSellIds, 'upsell'));
                        }else
                        if($columnName == 'attribute_set_id')
                        {
                        }else
                        if($columnName == 'associated_configure_ids')
                        {
                            //@TODO: Complex solutions should be done to implement this feature in product grid - waiting for next releases
                            $relatedIds = trim($columnValuesForUpdate[$itemId] ?? '') != "" ? explode(',', str_replace(' ','',trim($columnValuesForUpdate[$itemId] ?? '')) ?? '') : [];
                        }else
                        if($columnName == 'associated_groupped_ids')
                        {
                            $relatedIds = trim($columnValuesForUpdate[$itemId] ?? '') != "" ? explode(',', str_replace(' ','',trim($columnValuesForUpdate[$itemId] ?? '')) ?? '') : [];
                            $link = $this->_helperData->getRelatedLinks($relatedIds, [], $productId);
                            $product->setGroupedLinkData($link);

/*                            var_dump($columnValuesForUpdate[$itemId]);
                            var_dump($relatedIds);
                            var_dump($link);
                            var_dump($product->getGroupedLinkData());
                            echo '<pre>';
                            var_dump($product->getAssociatedProductsIds());
                            var_dump($product->getChildrenIds($product->getId()));
                            //get_class($product->getProductLinks()); //var_dump();
echo '</pre>';

                            echo \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED.'<br/>';
//                            if($product->isGrouped())
                            {
                                $product->setGroupedLinkData($link);
                            }
//                            else{
//echo 'product is not a grouped product: '.$product->getId().'<br/>';
//                            }
                            die();
*/
                        }else
                        if($columnName == 'tier_price' || $columnName == 'group_price')
                        {
                            /*
                            $originalPrices = $product->getData($columnName);
                            
                            foreach($columnValuesForUpdate[$productId] as $index => $groupPrice)
                            {
                                if(is_array($groupPrice) && $groupPrice['price'] && is_array($originalPrices) && $originalPrices[$index])
                                {
                                    $columnValuesForUpdate[$productId][$index]['price'] = Mage::helper('productupdater')->recalculatePrice($originalPrices[$index]['price'], $groupPrice['price']);
                                }
                            }
                            $product->setData($columnName, $columnValuesForUpdate[$productId]);
                            */
                        }else
                        if($columnName == 'type_id')
                        {
                        }else
                        // check attributes types
                        if(isset($attrs[$columnName]) && ($attrs[$columnName]->getFrontendInput() == 'price'))
                        {
                            $columnValuesForUpdate = $this->getRequest()->getParam($columnName);
                            if(trim($columnValuesForUpdate[$itemId])!="")
                            {
                                //$product->$columnName = $this->_helperData->recalculatePrice($product->$columnName, $columnValuesForUpdate[$itemId]);
                                //$product->$columnName = $this->_helperData->recalculatePrice($product->getData($columnName), $columnValuesForUpdate[$itemId]);
                                $product->setData($columnName, $this->_helperData->recalculatePrice($product->getData($columnName), $columnValuesForUpdate[$itemId]));
                            }else
                            {
                                $product->setData($columnName, $columnValuesForUpdate[$itemId]);
                                //$product->$columnName = null;
                            }
                        }else
                        if(isset($attrs[$columnName]) && ($attrs[$columnName]->getFrontendInput() == 'select'))
                        {
                            if($attrs[$columnName]->getIsRequired() && ($columnValuesForUpdate[$itemId] == ''))
                            {
                            }else
                            {
                                $product->setData($columnName, $columnValuesForUpdate[$itemId]);
                                //$product->$columnName =  $columnValuesForUpdate[$itemId];
                            }
                        }else
                        if(isset($attrs[$columnName]) && ($attrs[$columnName]->getFrontendInput() == 'media_image'))
                        {
                        }else
                        if(isset($attrs[$columnName]) && ($attrs[$columnName]->getFrontendInput() == 'image'))
                        {
                        }else
                        if($columnName != 'product' && $columnName != 'entity_id' && $columnName != 'category' && $columnName != 'websites' && $columnName != 'salable_qty')
                        //if(isset($product->$columnName))
                        {
                            $product->$columnName =  $columnValuesForUpdate[$itemId];
                            if($columnName != 'sku')
                            {
//                                $product->addAttributeUpdate($columnName, $columnValuesForUpdate[$itemId], $store);
                                $product->addAttributeUpdate($columnName, $columnValuesForUpdate[$itemId], $storeId);
                            }
                            $product->setData($columnName, $columnValuesForUpdate[$itemId]);
                        }
                        
                    }
                    $product->setStoreId($this->_helperData->getStoreId())->save();
                    $isUpdated ++;

                    /*
                     * Update Attribute Set for the product
                     */
                    $attributeSetValues = $this->getRequest()->getParam('attribute_set_id', null);

                    if($attributeSetValues && isset($attributeSetValues[$itemId]))
                    {
                        if($productBefore->getAttributeSetId() != $attributeSetValues[$itemId])
                        {
                            $product = $this->productRepository->getById($productId, true, $storeId);
                            $product
                                ->setAttributeSetId($attributeSetValues[$itemId])
                                ->setIsMassupdate(true)
                                ->save();
                        }
                    }

                    /**
                     * Categories Links update for a product
                     */
                    if($isCategoriesUpdated)
                    {
                        try {

                            // save products to categories links
                            $this->categoryLinkManagement->assignProductToCategories(
                                $product->getSku(),
                                $categoryIds
                            );

                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        }
                    }

                    /**
                     * Save stock inventory data
                     */
                    if($isInventoryValues)
                    {
                        $stockRegistry->updateStockItemBySku($product->getData('sku'), $stockItemDo);
                    }

                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s). %1', $e->getMessage()));
                }
            } 

            $this->messageManager->addSuccessMessage(__('A total %1 out of %2 record(s) have been updated.', $isUpdated, count($productIds)));
        }else
        {
            $this->messageManager->addErrorMessage($this->__('Please select product(s)').'. '.$this->__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
//        $this->_redirect('productmanage/*/index', array('_current' => true, '_query' => 'st=1'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('productmanage/*/', ['_current' => true, '_query' => 'st=1']); //'store' => $storeId]
    }
    
    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Iksanika_Productmanage::ma_update');
    }
    
}