<?php
namespace Magecomp\Whatsapppro\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\CustomerFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SMS_GENERAL_ENABLED = 'whatsapppro/general/enable';
    const SMS_ADMIN_MOBILE = 'admintemplate/admingeneral/mobile';
    const SMS_COUNTRY_VALIDATION = 'whatsapppro/wappcountryflag/countryvalidate';
    const COUNTRY_CODE_PATH = 'general/country/default';
    const COUNTRY_CODE_ALLOW = 'general/country/allow';
    const SMSCOUNTRYFLAG_GENERAL_ENABLED = 'whatsapppro/wappcountryflag/enable';
    const SMSCOUNTRYFLAG_BY_IP = 'whatsapppro/wappcountryflag/detect_by_ip';
    const SMSCOUNTRYFLAG_DETECT_BY_DEFAULT_COUNTRY = 'whatsapppro/wappcountryflag/defaultcountry';

    
    protected $_storeManager;
    protected $jsonHelper;
    protected $_customerFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }

    public function getStoreid()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    public function getStoreUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::SMS_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid()
        );
    }

    public function checkAdminNumber($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        return $this->scopeConfig->getValue(
            self::SMS_ADMIN_MOBILE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAdminNumber($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabledWhatsapp($storeId) && $this->checkAdminNumber($storeId) != '' && $this->checkAdminNumber($storeId) != null) {
            return $this->checkAdminNumber($storeId);
        }
    }

    public function checkCustomerExists($fieldValue, $fieldType = "mobile", $websiteId = 1)
    {
        $collection = $this->_customerFactory->create()->getCollection();
        if ($fieldType == "mobile") {
            $collection->addFieldToFilter("mobilenumber", $fieldValue);
        }

        if ($fieldType == "email") {
            $collection->addFieldToFilter("email", $fieldValue);
        }

        $collection->addAttributeToFilter('website_id', $websiteId);
        return $collection;
    }
    
    public function checkCustomerWithSameMobileNo($mobile, $websiteId = 1)
    {
        $customer = $this->checkCustomerExists($mobile, "mobile", $websiteId);
        if (count($customer) > 0) {
            return true;
        }
        return false;
    }
    
    public function getupdatedlt()
    {
        return $this->scopeConfig->getValue(
            self::MOBILELOGIN_UPDATE_DLTID,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getCountryvalidation($code)
    {
        $digit='';
        $validation = $this->scopeConfig->getValue(self::SMS_COUNTRY_VALIDATION, ScopeInterface::SCOPE_STORE, $this->getStoreid());
        $validation_array = json_decode($validation, true);
            $validation_value = array_values($validation_array);
        if ($this->isEnabled() && count($validation_value)>0) {
            $validation_value = $this->arraychange($validation_value);
        
            foreach ($validation_value as $key => $value) {
                if ($value['country'] == strtoupper($code)) {
                     $digit = $value['digit'];
                     return $digit;
                } elseif ($value['country'] == 1) {
                    $digit = $value['digit'];
                    return $digit;
                }
            }
        } else {
            return false;
        }
    }
    public function arraychange($array)
    {
        foreach ($array as $key => $val) {
            if ($val["country"] == 1) {
                $item = $array[$key];
                unset($array[$key]);
                array_push($array, $item);
                break;
            }
        }
        return $array;
    }
    public function isWhatsappEnable()
    {
        if ($this->isEnabled()) {
            $geoCountryCode = $this->getGeoCountryCode();
            if (!in_array($geoCountryCode, $this->getApplicableCountry())) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getAllowCountry()
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_ALLOW,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
    
    public function getGeoCountryCode()
    {
        try {
            $ipData = $this->jsonHelper->jsonDecode(
                $this->httpFile->fileGetContents(
                    "www.geoplugin.net/json.gp?ip=".$this->remoteAddress->getRemoteAddress()
                )
            );
            return $ipData->geoplugin_countryCode;
        } catch (\Exception $e) {
            return $this->scopeConfig->getValue(
                self::COUNTRY_CODE_PATH,
                ScopeInterface::SCOPE_WEBSITES
            );
        }
    }

    public function getApplicableCountry($isArray = true)
    {
        $_defaultCountry = [];
        $_country = [];
        $_defaultCountry[] = $this->getDefaultCountry();
        $_country = array_merge($_defaultCountry, explode(",", $this->getAllowCountry()));
        if (!$isArray) {
            $_country = $this->jsonHelper->jsonEncode($_country);
        }
        return $_country;
    }

    public function getDefaultCountry()
    {
        if ($this->getCountryFlagEnable() && $this->getCountryFlagDetectByIp()==0) {
            return $this->scopeConfig->getValue(
                self::SMSCOUNTRYFLAG_DETECT_BY_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_WEBSITES
            );
        }
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
    public function getCountryFlagEnable()
    {
        return $this->scopeConfig->getValue(
            self::SMSCOUNTRYFLAG_GENERAL_ENABLED,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
    public function getCountryFlagDetectByIp()
    {
        return $this->scopeConfig->getValue(
            self::SMSCOUNTRYFLAG_BY_IP,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
    public function getCountryFlagDefaultCountry()
    {
        return $this->scopeConfig->getValue(
            self::SMSCOUNTRYFLAG_DETECT_BY_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function isEnabledWhatsapp($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SMS_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
