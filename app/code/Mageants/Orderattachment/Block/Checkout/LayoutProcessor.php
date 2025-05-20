<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\Orderattachment\Block\Checkout;

use \Exception;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mageants\Orderattachment\Model\Attachment;
use Mageants\Orderattachment\Helper\Data;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Data
     */
    protected $dataHelper;
    
    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerSession $customerSession
     * @param Data $dataHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        Data $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     * @throws Exception
     */
    public function process($jsLayout)
    {
        if ($this->dataHelper->isOrderAttachmentEnabled()) {
            switch ($this->scopeConfig->getValue(
                Attachment::XML_PATH_ATTACHMENT_ON_DISPLAY_ATTACHMENT,
                ScopeInterface::SCOPE_STORE
            )) {
                case 'after-payment-methods':
                    $this->addToAfterPaymentMethods($jsLayout);
                    break;
    
                case 'after-shipping-methods':
                    $this->addToAfterShippingMethods($jsLayout);
                    break;
            }
        }

        return $jsLayout;
    }

    /**
     * AddToAfterPaymentMethods method
     *
     * @param array $jsLayout
     * @return array
     */
    protected function addToAfterPaymentMethods(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                 ['children']['payment']['children']['afterMethods']['children'])) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                       ['children']['payment']['children']['afterMethods']['children'];
            
            $fields['order-attachment-after-payment-methods'] =
            ['component' => "Mageants_Orderattachment/js/view/order/payment/payment-attachment"];
        }

        return $jsLayout;
    }

    /**
     * AddToAfterShippingMethods Method
     *
     * @param array $jsLayout
     * @return array
     */
    protected function addToAfterShippingMethods(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children'])) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children'];

            $fields['order-attachment-after-shipment-methods'] =
            [
                'component' => "uiComponent",
                'displayArea' => "shippingAdditional",
                'children' =>
                ['attachment'=> ['component' => "Mageants_Orderattachment/js/view/order/shipment/shipment-attachment"]]
                ];
        }

         return $jsLayout;
    }
}
