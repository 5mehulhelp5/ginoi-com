<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Helper;


/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    protected $categoryPath = array();

    public $_escaper;
    public $_storeManager;
    public $_categoryFactory;
    private $_helper;
    private $_category;
    private $categoryTreeFactory;
    private $categoryTree;
    private $_categoryRepository;
    private $collectionFactory;
    private $_tree;
    public $_categoryCollectionFactory;



    private $_category_collection;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Iksanika\Productmanage\Helper\Data $helper,
        \Magento\Catalog\Model\Category $category,
        \Magento\Store\Model\StoreManagerInterface $storeManager,

        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,

        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Catalog\Block\Adminhtml\Category\Tree $tree,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {

        parent::__construct($context);
//        \Magento\Framework\DataObject
        $this->_escaper = $escaper;
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_helper = $helper;
        $this->_category = $category;
        $this->categoryTreeFactory = $categoryTreeFactory;
        $this->categoryTree = $categoryTree;
        $this->collectionFactory = $collectionFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->_tree = $tree;

        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }


    /* version before v2.0.11

    public function generateCategoryPath($category)
    {
        if($category->getName())
        {
            $this->categoryPath[] = [
                'id'    => $category->getId(),
                'level' => $category->getLevel(),
                'name'  => $category->getName(),
            ];
        }
        if($category->hasChildren())
        {
            foreach($category->getChildrenCategories() as $child)
            {
                $this->generateCategoryPath($child);
            }
        }
    }

    public function generateCategoryPath2($node)
    {
//        var_dump($node->getData());
//        if($node->getName())
        {
            $this->categoryPath[] = [
                'id'    => $node->getId(),
                'level' => $node->getLevel(),
                'name'  => $node->getName(),
            ];
        }

        if($node->hasChildren())
        {
            $list = $node->getChildren();

            foreach($list as $child)
            {
                $this->generateCategoryPath2($child);
            }
        }
    }
    */





    public function getCategoriesOptionsForFilter()
    {
        /* version before v2.0.11
        $parentCategory = $this->_categoryRepository->get(\Magento\Catalog\Model\Category::TREE_ROOT_ID); // it is original call in latest version
        $this->generateCategoryPath($parentCategory);
        */
        $rootId             = $this->getRootId();
        $this->categoryPath = [];
        $this->getCategoriesTree();

        $options    =   [0 => __('[NO CATEGORY]')];
        foreach($this->categoryPath as $category)
        {
            if($rootId != $category['id'])
            {
//            $string = str_repeat(". ", max(0, ($path['level'] - 1) * 3)) . $path['name'] . ' ['.$path['id'].']';
                $string = str_repeat(". ", max(0, ($category['level']) * 2)) . $category['name'] ; // &#183; &#8226; // . ' => '. $path['path']
                $options[$category['id']] = $string;
            }
        }
        return $options;
    }






    /*  new since v2.0.11: start*/
    public function getCategoriesTree()
    {
        $rootArray = $this->_getNodeJson($this->getRoot());
        $tree = $rootArray['children'] ?? [];
        return $tree;
    }


    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['id']         = $node->getId();
        $item['level']      = $node->getLevel();
        $item['name']       = $this->buildNodeName($node);

        $item['store']      = (int)$this->getStore()->getId();
        $item['path']       = $node->getData('path');
        $item['position']   = $node->getData('position');


        $this->categoryPath[] = [
            'id'        => $node->getId(),
            'level'     => $node->getLevel(),
            'name'      => $this->buildNodeName($node),
            'path'      => $item['path'],
            'position'  => $item['position'],
        ];


        if ($node->hasChildren()) {
            $item['children'] = [];
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child);
            }
        }

        return $item;
    }

    /**
     * Get category name
     *
     * @param DataObject $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        $result .= ' [ID: ' . $node->getId() . ']';
        $result .= ' (' . $node->getProductCount() . ')';
        return $result;
    }




    public function getRootId()
    {
        $storeId = (int)$this->getRequest()->getParam('store');

        if ($storeId) {
            $store = $this->_storeManager->getStore($storeId);
            $rootId = $store->getRootCategoryId();
        } else {
            $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        }
        return $rootId;
    }

    /**
     * Get root category for tree
     *
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRoot()
    {
        $rootId = $this->getRootId();
        $tree   = $this->categoryTree->load();

//        if ($this->getCategory()) {
//            $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
//        }

        $tree->addCollectionData($this->getCategoryCollection());

        $root = $tree->getNodeById($rootId);

        if ($root) {
            $root->setIsVisible(true);
            if($root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }
        }
        return $root;
    }


    /**
     * Get category collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCategoryCollection()
    {
        if($this->_category_collection === null) {
            $storeId    = $this->getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $collection = $this->_categoryFactory->create()->getCollection();

            $collection->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'is_active'
            )->setProductStoreId(
                $storeId
            )->setLoadProductCount(
                true //$this->_withProductCount
            )->setStoreId(
                $storeId
            );
            $this->_category_collection = $collection;
        }
        return $this->_category_collection;
    }



    /**
     * Return parent categories of category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Framework\DataObject[]
     */
    public function getParentCategories($category)
    {

//        $pathIds = array_reverse(explode(',', (string)$category->getPathInStore()));
//        $pathIds = array_reverse($category->getPathIds());
        $pathIds = $category->getPathIds();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories = $this->_categoryCollectionFactory->create();
        return $categories->setStore(
            $this->_storeManager->getStore()
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'url_key'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        )->load()->getItems();
    }


    /**
     * Get request
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }


    /**
     * Get store from request
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore((int)$this->getRequest()->getParam('store'));
    }


    /**
     * Escape HTML entities
     *
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     * @see Escaper Usage
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->_escaper->escapeHtml($data, $allowedTags);
    }

    /*  new since v2.0.11: end */






























    /**
     * Genarate category structure with all categories
     *
     * @param int $rootId root category id
     * @return array sorted list category_id=>title
     */
    /* version since 2.0.11 - adjusted to enhanced categories tree (full categories tree of the magento) */
//    public function getTree($rootId)
    public function getTree()
    {
        $rootId             = $this->getRootId();
        $tree               = array();
        $position           = array();
        $this->categoryPath = [];

        $this->getCategoriesTree();

        foreach ($this->categoryPath as $category)
        {
            if (($rootId != $category['id']) && $category['level'])
            {
                $tree[$category['id']] = array(
                    'label' => str_repeat('. . ', $category['level']) . $category['name'],
                    'value' => $category['id'],
                    'path'  => explode('/', $category['path'] ?? ''),
                );
            }
            $position[$category['id']] = $category['position'];
        }
        
        foreach ($tree as $catId => $category)
        {
            $order = array();
            foreach ($category['path'] as $id)
            {
		        $order[] = isset($position[$id]) ? $position[$id] : 0;
            }
            $tree[$catId]['order'] = $order;
        }
        
        usort($tree, array($this, 'compare'));
        
        return $tree;
    }

    /**
     * Generate category structure with all categories
     *
     * @param int $rootId root category id
     * @return array sorted list category_id=>title
     */
    /* version before v2.0.11
    public function getTree($rootId)
    {
        $tree               =   array();
        $categoryCollection =   $this->_category->getCollection()->addNameToResult();

        $position = array();
        foreach ($categoryCollection as $category)
        {
            $path = explode('/', $category->getPath());
            if ((!$rootId || in_array($rootId, $path)) && $category->getLevel() && $category->getName())
            {
                $tree[$category->getId()] = array(
                    'label' => str_repeat('. . ', $category->getLevel()) . $category->getName() . ' ['.$category->getId().']',
                    'value' => $category->getId(),
                    'path'  => $path,
                );
            }
            $position[$category->getId()] = $category->getPosition();
        }

        foreach ($tree as $catId => $category)
        {
            $order = array();
            foreach ($category['path'] as $id)
            {
                $order[] = isset($position[$id]) ? $position[$id] : 0;
            }
            $tree[$catId]['order'] = $order;
        }

        usort($tree, array($this, 'compare'));

        return $tree;
    }
    */
    /**
     * Compares category data
     *
     * @return int 0, 1 , or -1
     */
    public function compare($a, $b)
    {
        foreach ($a['path'] as $index => $id)
        {
            if (!isset($b['path'][$index]))
                return 1; // B path is shorther then A, and values before were equal
            if ($b['path'][$index] != $id)
                return ($a['order'][$index] < $b['order'][$index]) ? -1 : 1; // compare category positions at the same level
        }
        return ($a['value'] == $b['value']) ? 0 : -1; // B path is longer or equal then A, and values before were equal
    }      
    
}
