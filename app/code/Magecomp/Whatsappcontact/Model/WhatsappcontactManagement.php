<?php

namespace Magecomp\Whatsappcontact\Model;

use Magecomp\Whatsappcontact\Api\DataInterface;
use Magecomp\Whatsappcontact\Api\WhatsappcontactManagementInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;

class WhatsappcontactManagement implements WhatsappcontactManagementInterface, DataInterface
{
    protected $helperData;
    protected $storeRepository;
    protected $_modelCategoryFactory;
    public function __construct(
        \Magecomp\Whatsappcontact\Helper\Data $helperData,
        CategoryFactory $modelCategoryFactory,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->helperData = $helperData;
        $this->storeRepository = $storeRepository;
        $this->_modelCategoryFactory = $modelCategoryFactory;
    }
    public function getConfigData($storeid)
    {
        try {
            if ($this->helperData->getConfig(self::WCONTACT_ENABLED,$storeid)) {   
               $data = [
                "status" => true,
                "Enable" => $this->helperData->getConfig(self::WCONTACT_ENABLED,$storeid),
                "Enable Only In Mobile  " => $this->helperData->getConfig(self::WCONTACT_ONLY_MOBILE,$storeid),
                "Enter WhatsApp Phone Number" => $this->helperData->getConfig(self::WCONTACT_MOBILE,$storeid),
                "WhatsApp Message" => $this->helperData->getConfig(self::WCONTACT_MSG,$storeid),
                "Button Color" => $this->helperData->getConfig(self::WCONTACT_BTN_COLOR,$storeid),
                "Button Icon Color" => $this->helperData->getConfig(self::WCONTACT_ICON_COLOR,$storeid),
                "Button Top" => $this->helperData->getConfig(self::WCONTACT_TOP,$storeid),
                "Button Left" => $this->helperData->getConfig(self::WCONTACT_LEFT,$storeid),
                "Button Right" => $this->helperData->getConfig(self::WCONTACT_RIGHT,$storeid),
                "Button Bottom" => $this->helperData->getConfig(self::WCONTACT_BOTTOM,$storeid),
                "Button Bottom Animation" => $this->helperData->getConfig(self::WCONTACT_BTN_ANIMATION,$storeid),  
            ];  
                
            } else {
                $data = ["status" => false, "errormessage" => __("Please Enable The Extension")];
            }
        } catch (\Exception $e) {
            $data = ["status" => false, "errormessage" => __($e->getMessage())];
        }
        return json_encode($data);
    }
}
