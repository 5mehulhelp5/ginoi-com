<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer number
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    protected static $showImagesUrl = null;
    protected static $showByDefault = null;
    
    protected static $imagesWidth = null;
    protected static $imagesHeight = null;
    protected static $imagesScale = null;
    
    protected $_helper;
    protected $_imageHelper;
    protected $_scopeConfig;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, 
            \Iksanika\Productmanage\Helper\Data $extensionHelper,
            \Magento\Catalog\Helper\Image $catalogImage,
            array $data = [])
    {
        $this->_helper = $extensionHelper;
        $this->_imageHelper = $catalogImage;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    public function initSettings()
    {
        if(!self::$showImagesUrl)
            self::$showImagesUrl = (int)$this->_scopeConfig->getValue('iksanika_productmanage/images/showurl') === 1;
        if(!self::$showByDefault)
            self::$showByDefault = (int)$this->_scopeConfig->getValue('iksanika_productmanage/images/showbydefault') === 1;
        if(!self::$imagesWidth)
            self::$imagesWidth = $this->_scopeConfig->getValue('iksanika_productmanage/images/width');
        if(!self::$imagesHeight)
            self::$imagesHeight = $this->_scopeConfig->getValue('iksanika_productmanage/images/height');
        if(!self::$imagesScale)
            self::$imagesScale = $this->_scopeConfig->getValue('iksanika_productmanage/images/scale');
    }



/*

    if($productItem && $productItem->getData('small_image') && ($productItem->getData('small_image') != 'no_selection') && ($productItem->getData('small_image') != ""))
    {
        $outImagesWidth = self::$imagesWidth ? "width='".self::$imagesWidth."'":'';
        if(self::$imagesScale)
            $outImagesHeight = (self::$imagesHeight) ? "height='".self::$imagesHeight."'":'';
        else
            $outImagesHeight = (self::$imagesHeight && !self::$imagesWidth) ? "height='".self::$imagesHeight."'":'';
    //return get_class($this->_catalogImage);


    //            $this->helper('Magento\Catalog\Helper\Product')->getImageUrl($product);
    //            $imgUrl = $this->_catalogImage->init($productItem,'cart_page_product_thumbnail') ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)->setImageFile($productItem->getImage())->getUrl();
        $imgUrl = $this->_catalogImage->init($productItem,'cart_page_product_thumbnail')->setImageFile($productItem->getImage())->getUrl();
    //cart_page_product_thumbnail

        return '<img src="'.$imgUrl.'" '.$outImagesWidth.' '.$outImagesHeight.' alt="" />';
    //            return '<img src="'.($this->_catalogImage->init($productItem, "small_image")->resize(self::$imagesWidth)).'" '.$outImagesWidth.' '.$outImagesHeight.' alt="" />';
    }else
    {
        return '[NO IMAGES]';
    }
*/



    /**
     * Returns value of the row
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed|string
     */
    protected function _getValue(\Magento\Framework\DataObject $row)
    {
        $this->initSettings();
        
        $data = parent::_getValue($row);

        $noSelection    =   false;
        $dored          =   false;
        if ($getter = $this->getColumn()->getGetter())
        {
            $val = $row->$getter();
        }

        $_filename = $val = $val2 = $row->getData($this->getColumn()->getIndex());
        $noSelection = ($val == 'no_selection' || $val == '') ? true : $noSelection;
        $url = $val ? $this->_helper->getImageUrl($val) : '';

        if(!$val || !$this->_helper->getFileExists($val))
        {
          $dored = true;
          $val .= "[*]";
        }

        $dored = (strpos($val, "placeholder/")) ? true : $dored;
        $filename = (!self::$showImagesUrl) ? '' : substr($val2, strrpos($val2, "/")+1, strlen($val2)-strrpos($val2, "/")-1);

        $val = ($dored) ?
                ("<span style=\"color:red\" id=\"img\">$filename</span>") :
                "<span>". $filename ."</span>";

//        $out = (!$noSelection) ?
//            ($val. '<center><a href="#" onclick="window.open(\''. $url .'\', \''. $val2 .'\')" title="'. $val2 .'" '. ' url="'.$url.'" id="imageurl">') :
//            '';

        $out = (!$noSelection) ?
            ($val. '') :
            '';
/*
        $outImagesWidth = self::$imagesWidth ? "width='".self::$imagesWidth."'":'';
        if(self::$imagesScale)
            $outImagesHeight = (self::$imagesHeight) ? "height='".self::$imagesHeight."'":'';
        else
            $outImagesHeight = (self::$imagesHeight && !self::$imagesWidth) ? "height='".self::$imagesHeight."'":'';
*/
        try {
            // temporary image
            $resizedImage = $this->resizeImage($row, 'product_base_image', 10, 10);


            $img = $this->_imageHelper->init($row, $this->getColumn()->getIndex())->setImageFile($_filename); // $_filename instead of $row->getImage()

            if($this->_helper->getFileExists($_filename) && (trim($_filename) != ''))
            {
//                $url = $img->setImageFile($_filename)->getUrl(); // $_filename instead of $row->getImage()
                $url = $img->getUrl(); // $_filename instead of $row->getImage()


                //temporary image
                $url = $resizedImage->getUrl();


            }else{
                $url = $this->_imageHelper->getDefaultPlaceholderUrl('image');
            }

//            $imgR = $img->resize(self::$imagesWidth);
//            "<img src=\"".$url."\" border=\"0\" class=\"admin__control-thumbnail\" data-mage-init='{\"apmImage\": {\"src\":\"".$url."\", \"org_src\":\"".$url."\", \"alt\":\"".$img->getLabel()."\", \"link\":\"#\"}}'/>" :
            $out .= (!$noSelection) ?
                    "<img src=\"".$url."\" border=\"0\" class=\"admin__control-thumbnail\" data-mage-init='{\"apmImage\": {\"src\":\"".$url."\", \"org_src\":\"".$url."\", \"alt\":\"\", \"link\":\"#\"}}'/>" :
                    "<center><strong>[".__('NO IMAGE')."]</strong></center>";

//            <img  data-bind="attr: {src: $col.getSrc($row()), alt: $col.getAlt($row())}" src="" alt="">
//              data-bind=\"click: test(), alt: \"".$img->getLabel()."\" \"
        }catch(\Exception $e)
        {
            $out .= "<center><strong>[".__('NO IMAGE')."]</strong></center>";
        }

        return $out;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @see \Magento\Catalog\Model\Product\Image
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resizeImage($product, $imageId, $width, $height = null)
    {
        $resizedImage = $this->_imageHelper
            ->init($product, $imageId)
            ->constrainOnly(TRUE)
            ->keepAspectRatio(TRUE)
            ->keepTransparency(TRUE)
            ->keepFrame(FALSE)
            ->resize($width, $height);
        return $resizedImage;
    }



    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
//    public function render(\Magento\Framework\DataObject $row)
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->_getValue($row);
    }

    /**
     * Renders CSS
     *
     * @return string
     */
    public function renderCss()
    {
        return parent::renderCss() . ' col-number';
    }
    
}
