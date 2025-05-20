<?php

namespace Magecomp\Whatsappcontact\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const WCONTACT_ENABLED = 'whatsappcontact/general/enable';
    const WCONTACT_ONLY_MOBILE = 'whatsappcontact/general/onlymobile';
    const WCONTACT_BTN_COLOR = 'whatsappcontact/general/btncolor';
    const WCONTACT_ICON_COLOR = 'whatsappcontact/general/iconcolor';
    const WCONTACT_TOP = 'whatsappcontact/general/top';
    const WCONTACT_LEFT = 'whatsappcontact/general/left';
    const WCONTACT_RIGHT = 'whatsappcontact/general/right';
    const WCONTACT_BOTTOM = 'whatsappcontact/general/bottom';
    const WCONTACT_BTN_ANIMATION = 'whatsappcontact/general/btnanimation';
    const WCONTACT_MOBILE = 'whatsappcontact/general/mobile';
    const WCONTACT_MSG = 'whatsappcontact/general/msg';

    protected $_modelStoreManagerInterface;
    protected $_frameworkRegistry;

    public function __construct(
        Context $context,
        StoreManagerInterface $modelStoreManagerInterface,
        Registry $frameworkRegistry
    ) {
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->_frameworkRegistry = $frameworkRegistry;

        parent::__construct($context);
    }

    public function isMobileDevice()
    {
        $aMobileUA = [
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        ];

        foreach ($aMobileUA as $sMobileKey => $sMobileOS) {
            if (preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])) {
                return true;
            }
        }
        return false;
    }
    public function getWhatsaaplink()
    {
        $whatsaapurl = "";
        if ($this->isMobileDevice()) {
            //$whatsaapurl = 'whatsapp://';
            $whatsaapurl = 'https://api.whatsapp.com/';
        } else {
            $whatsaapurl = 'https://web.whatsapp.com/';
        }

        return $whatsaapurl;
    }
      public function getConfig($path, $storeid = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeid
        );
    }
    
    public function getData()
    {
        $btnColor = $this->scopeConfig->getValue(self::WCONTACT_BTN_COLOR, ScopeInterface::SCOPE_STORE);
        $iconColor = $this->scopeConfig->getValue(self::WCONTACT_ICON_COLOR, ScopeInterface::SCOPE_STORE);
        $top = $this->scopeConfig->getValue(self::WCONTACT_TOP, ScopeInterface::SCOPE_STORE) . 'px';
        $left = $this->scopeConfig->getValue(self::WCONTACT_LEFT, ScopeInterface::SCOPE_STORE) . 'px';
        $right = $this->scopeConfig->getValue(self::WCONTACT_RIGHT, ScopeInterface::SCOPE_STORE) . 'px';
        $bottom = $this->scopeConfig->getValue(self::WCONTACT_BOTTOM, ScopeInterface::SCOPE_STORE) . 'px';
        $btnAnimation = $this->scopeConfig->getValue(self::WCONTACT_BTN_ANIMATION, ScopeInterface::SCOPE_STORE);
        $mobileNumber = $this->scopeConfig->getValue(self::WCONTACT_MOBILE, ScopeInterface::SCOPE_STORE);
        $message = $this->scopeConfig->getValue(self::WCONTACT_MSG, ScopeInterface::SCOPE_STORE);
        

        $html = '';
        if ($this->scopeConfig->getValue(self::WCONTACT_ENABLED, ScopeInterface::SCOPE_STORE)) {
            if ($this->scopeConfig->getValue(self::WCONTACT_ONLY_MOBILE, ScopeInterface::SCOPE_STORE)) {
                if (!($this->isMobileDevice())) {
                    return $html;
                }
            }

            $url = $this->getWhatsaaplink();
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
	    $request = $objectManager->get('\Magento\Framework\App\Request\Http');
	    if($request->getFullActionName() == 'cms_index_index' || $request->getFullActionName() == 'mpfaqs_article_index'){
	    $html .= '<a 
			            href="' . $url . 'send?l=en&amp;phone=' . $mobileNumber . '&text=' . $message . '" 
						class="whatsapp_a ' . $btnAnimation . '" 
						style="right: ' . $right . ';
						       top : ' . $top . ';
						       left:' . $left . '; 
						       bottom:' . $bottom . '; 
							   background-color:' . $btnColor . ';" 
							   target="_blank">
	                    <i class="fa-brands fa-whatsapp" style="color:' . $iconColor . '"></i></a>';
	    }
	    else{
            $html .= '<a 
			            href="' . $url . 'send?l=en&amp;phone=' . $mobileNumber . '&text=' . $message . '" 
						class="whatsapp_a ' . $btnAnimation . '" 
						style="right: ' . $right . ';
						       top : ' . $top . ';
						       left:' . $left . '; 
						       bottom:' . $bottom . '; 
							   background-color:' . $btnColor . ';" 
							   target="_blank">
	                    <i class="fa fa-whatsapp" style="color:' . $iconColor . '"></i></a>';
	                    }
        }
        return $html;
    }
}
