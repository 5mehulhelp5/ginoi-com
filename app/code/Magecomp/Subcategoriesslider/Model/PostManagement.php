<?php
namespace Magecomp\Subcategoriesslider\Model;
use Magecomp\Subcategoriesslider\Api\PostManagementInterface;
use Magecomp\Subcategoriesslider\Helper\Data;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;



class PostManagement implements PostManagementInterface
{
    protected $repository;
    protected $helper;
    protected $StoreRepositoryInterface;
    protected $_categoryCollectionFactory;
        
    public function __construct(
        Data $helper,
        CategoryRepository $repository ,StoreRepositoryInterface $StoreRepositoryInterface,CollectionFactory $categoryCollectionFactory
    )
    {
        $this->helper = $helper;
        $this->repository = $repository;
        $this->StoreRepositoryInterface = $StoreRepositoryInterface;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

   public function setSubcategorySlider($maincategoryid, $subcategoryid, $storeid)
    {
        try {

             if (!$this->helper->isEnabled($storeid)) {
                $response = [
                    'status' => false,
                    'message' => "Please Enable Extension."
                ];
                return json_encode($response);
            }
            // Get the list of stores
            $stores = $this->StoreRepositoryInterface->getList();
            foreach ($stores as $store) {
                $store_ids[] = $store->getId();
            }

            // Check if the provided store ID is valid
            if (!in_array($storeid, $store_ids)) {
                return json_encode([
                    'status' => false,
                    'message' => "Enter Valid Store Id."
                ]);
            }

            // Get the main category by ID
            $category = $this->repository->get($maincategoryid);

            // Check if the main category has any child categories
            if (!$category->hasChildren()) {
                return json_encode([
                    'status' => false,
                    'message' => "No any child category."
                ]);
            }

            // Validate if the subcategory ID exists and belongs to the main category
            $subcategoryids = explode(",", $subcategoryid);
            $categoryCollection = $this->_categoryCollectionFactory->create();
            $categoryCollection->addAttributeToSelect('name');
            $categoryCollection->addAttributeToFilter('entity_id', ['in' => $subcategoryids]);
            $categoryCollection->addAttributeToFilter('parent_id', $maincategoryid);

            if ($categoryCollection->getSize() == count($subcategoryids)) {
                $category->setData("Subcategoriessliderslider", $subcategoryids);
                $category->setData("store_id", $storeid);
                $category->save();

                // Prepare success response
                return json_encode([
                    'status' => true,
                    'message' => "Sub category saved to the slider."
                ]);
            } else {
                return json_encode([
                    'status' => false,
                    'message' => "Invalid subcategory ID."
                ]);
            }

        } catch (\Exception $e) {
            // Handle any exceptions
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function setSubcategoryList($maincategorylistid, $subcategorylistid, $storeid)
    {
        try {
            if (!$this->helper->isEnabled($storeid)) {
                $response = [
                    'status' => false,
                    'message' => "Please Enable Extension."
                ];
                return json_encode($response);
            }
            if (!$this->helper->getSubcategorylist($storeid)) {
                $response = [
                    'status' => false,
                    'message' => "Please Enable Subcategories Listview."
                ];
                return json_encode($response);
            }
            $stores = $this->StoreRepositoryInterface->getList();
            foreach ($stores as $store) {
                $store_ids[] = $store->getId();
            }
            if (!in_array($storeid, $store_ids)) {
                $response = [
                    'status' => false,
                    'message' => "Enter Valid Store Id."
                ];
                return json_encode($response);
            }

            if ($this->helper->getSubcategorylist($storeid)) {
                $categoryid = $maincategorylistid;
                $category = $this->repository->get($categoryid);

                // Check if the category has any child categories
                if (!$category->hasChildren()) {
                    $response = [
                        'status' => false,
                        'message' => "No any child category."
                    ];
                    return json_encode($response);
                }

            $subcategoryids = explode(",", $subcategorylistid);
            $categoryCollection = $this->_categoryCollectionFactory->create();
            $categoryCollection->addAttributeToSelect('name');
            $categoryCollection->addAttributeToFilter('entity_id', ['in' => $subcategoryids]);
            $categoryCollection->addAttributeToFilter('parent_id', $maincategorylistid);

            if ($categoryCollection->getSize() == count($subcategoryids)) {
                $category->setData("Subcategoriessliderlist", $subcategoryids);
                $category->setData("store_id", $storeid);
                $category->save();

                // Prepare success response
                return json_encode([
                    'status' => true,
                    'message' => "Sub category saved for List view."
                ]);
            } else {
                return json_encode([
                    'status' => false,
                    'message' => "Invalid subcategory ID."
                ]);
            }
        }
        }
         catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        return json_encode($response);
    }


  public function getConfigData($storeid)
  {
   
    try{
        if ($this->helper->isEnabled($storeid)) {
               
            $responseData = [
                "Enable" => $this->helper->isEnabled($storeid),
                "Image" => $this->helper->isImage($storeid),
                "Background Color" => $this->helper->getBackgroundcolor($storeid),
                "Font Color" => $this->helper->getFontcolor($storeid),
                "Subcategories Listview" => $this->helper->getSubcategorylist($storeid)
            ];
            $response = ["status" => true, "data" => $responseData];
        } else {
            $response = ["status" => false, "response" => __("Please Enable The Extension")];
        }
        return json_encode($response);

    } catch (\Exception $e) {
        $response = ["status" => false, "response" => __($e->getMessage())];
        return json_encode($response);
    }
  }

}
