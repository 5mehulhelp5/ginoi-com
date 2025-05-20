<?php
namespace Magecomp\Whatsapppro\Model\Checkout;

class LayoutProcessorplugin
{
    protected $_helper;

    public function __construct(
        \Magecomp\Whatsapppro\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['notice'] = __('Enter Your WhatsApp No. With Country Code i.e 919898989898');
        if ($this->_helper->getCountryFlagEnable()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['notice'] = __('Enter Your WhatsApp Number.');
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['tooltip']['description'] = __('For Order Updates.');
        }
        return $jsLayout;
    }
}
