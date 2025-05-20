<?php

namespace Magecomp\Subcategoriesslider\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected $_storeManager;
    const GENRAL_ENABLE = 'subcategoriesslider/general/enable';
    const GENRAL_IMAGEID = 'subcategoriesslider/general/upload_image_id';
    const GENRAL_BACKGROUNDCOLOR = 'subcategoriesslider/general/backgroundcolor';
    const GENRAL_FONTCOLOR = 'subcategoriesslider/general/fontcolor';
    const GENRAL_SUBCATEGORYLIST = 'subcategoriesslider/general/subcategorylist';
    const GENRAL_SUBCATEGORYID = 'subcategoriesslider/general/categoryid';

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function isEnabled($storeid=null)
    {
        $store = $this->_storeManager->getStore();
        $configValue = $this->scopeConfig->getValue(
            self::GENRAL_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }

    public function isImage($storeid=null)
    {
        $store = $this->_storeManager->getStore();
        $configValue = $this->scopeConfig->getValue(
            self::GENRAL_IMAGEID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }

    public function getBackgroundcolor($storeid=null)
    {
        $store = $this->_storeManager->getStore();
        $configValue = $this->scopeConfig->getValue(
            self::GENRAL_BACKGROUNDCOLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }

    public function getFontcolor($storeid=null)
    {
        $store = $this->_storeManager->getStore();
        $configValue = $this->scopeConfig->getValue(
            self::GENRAL_FONTCOLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }

    public function getSubcategorylist($storeid=null)
    {
        $store = $this->_storeManager->getStore();
        $configValue = $this->scopeConfig->getValue(
            self::GENRAL_SUBCATEGORYLIST,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }

    public function getSubcategoryid($storeid=null)
    {
        $store = $this->_storeManager->getStore();
        $configValue = $this->scopeConfig->getValue(
            self::GENRAL_SUBCATEGORYID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }

    public function isMobileDevice()
    {
        $aMobileUA = array(
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach($aMobileUA as $sMobileKey => $sMobileOS){
            if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
                return true;
            }
        }
        return false;
    }

}
