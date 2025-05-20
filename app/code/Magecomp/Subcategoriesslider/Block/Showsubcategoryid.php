<?php

namespace Magecomp\Subcategoriesslider\Block;
use Magecomp\Subcategoriesslider\Helper\Data ;
use Magento\Store\Model\StoreManagerInterface ;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry ;
use Magento\Framework\Filesystem ;
use Magento\Framework\Image\AdapterFactory ;
use Magento\Framework\View\Asset\Repository ;
use Magento\Catalog\Helper\ImageFactory ;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\CategoryFactory;

class Showsubcategoryid extends \Magento\Framework\View\Element\Template
{
    protected $helperData;
    protected $_storedata;
    protected $_categorydata;
    protected $_registrydata;
    protected $_filesystem;
    protected $_directorylist;
    protected $_imageFactory;
    protected $_assetRepos;
    protected $_helperImageFactory;
    protected $categoryfactory;

    public function __construct(
        Context $context,
        Data $helperData,
        StoreManagerInterface $storeManager,
        Category $category,
        Registry $registry,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        CategoryFactory $categoryfactory,
        Repository $assetRepos,
        ImageFactory $helperImageFactory
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->_storedata = $storeManager;
        $this->_categorydata = $category;
        $this->_registrydata = $registry;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->categoryfactory = $categoryfactory;
        $this->_assetRepos = $assetRepos;
        $this->_helperImageFactory = $helperImageFactory;
    }
    public function isEnabled()
    {
        return $this->helperData->isEnabled();
    }

    public function isImage()
    {
        return $this->helperData->isImage();
    }

    public function getMediaUrl()
    {
        $currentStore = $this->_storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    public function getBaseUrl()
    {
        $baseUrl = $this->_storedata->getStore()->getBaseUrl();;
        return $baseUrl;
    }

    public function getdefaultimage()
    {
        $imagePlaceholder = $this->_helperImageFactory->create();
        return $this->_assetRepos->getUrl($imagePlaceholder->getPlaceholder('image'));
    }

    public function getBackgroundcolor()
    {
        return $this->helperData->getBackgroundcolor();
    }

    public function getFontcolor()
    {
        return $this->helperData->getFontcolor();
    }

    public function getSubcategorylist()
    {
        return $this->helperData->getSubcategorylist();
    }

    public function getSlidervalue()
    {
        $subcat_id = array();
        $cate = $this->_categorydata->load($this->helperData->getSubcategoryid());
        $subCategories = $cate->getChildrenCategories();
        foreach($subCategories as $subCategory) {
            $subcat_id[] = $subCategory['entity_id'];
        }
        return $subcat_id;
    }

    public function getListvalue()
    {
        $subcat_id = array();
        $cate = $this->_categorydata->load($this->helperData->getSubcategoryid());
        $subCategories = $cate->getChildrenCategories();
        foreach($subCategories as $subCategory) {
            $subcat_id[] = $subCategory['entity_id'];
        }
        return $subcat_id;
    }

    public function getDataforvalue($id)
    {
        $data = $this->categoryfactory->create()->load($id);
        return $data;
    }

    public function getCountslider()
    {
        $subcat_id = array();
        $cate = $this->_categorydata->load($this->helperData->getSubcategoryid());
        $subCategories = $cate->getChildrenCategories();
        foreach($subCategories as $subCategory) {
            $subcat_id[] = $subCategory['entity_id'];
        }
        return count($subcat_id);
    }

    public function getCountlist()
    {
        $subcat_id = array();
        $cate = $this->_categorydata->load($this->helperData->getSubcategoryid());
        $subCategories = $cate->getChildrenCategories();
        foreach($subCategories as $subCategory) {
            $subcat_id[] = $subCategory['entity_id'];
        }
        return $subcat_id;
    }

    public function resize($image, $width, $height)
    {
        $position = strpos($image, 'pub/');
        if ($position !== false) {
            $image = substr($image, $position);
        }
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)->getAbsolutePath($image);


        $position = strpos($image, 'media/');
        if($position !== false) {
            // Get the substring starting from 'pub/'
            $image = substr($image, $position);
        }
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::PUB)->getAbsolutePath($image);

        if (!file_exists($absolutePath)) return false;

        $ext  = pathinfo($image, PATHINFO_EXTENSION);
        if($ext == "gif"){
            return $this->getBaseUrl() . "pub/" . $image;
        }

        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/' . $width . $image);

        
        if (!file_exists($imageResized)) {
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(FALSE);
            $imageResize->resize($width, $height);
            $destination = $imageResized;
            $imageResize->save($destination);
        }
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/' . $width . $image;
        return $resizedURL;
    }


    public function resizes($image, $width = null, $height = null)
    {   
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('subcategoriesslider/image/') . $image;

        if (!file_exists($absolutePath)) return false;

        $ext  = pathinfo($image, PATHINFO_EXTENSION);
        if($ext == "gif"){
            return $this->getBaseUrl()."pub/media/subcategoriesslider/image/".$image;
        }

        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/' . $width) . $image;
        if (!file_exists($imageResized)) {
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(false);
            $imageResize->resize($width, $height);
            $destination = $imageResized;
            $imageResize->save($destination);
        }
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/' . $width  . $image;
        return $resizedURL;
    }

    public function isMobileDevice()
    {
        return $this->helperData->isMobileDevice();
    }
}

