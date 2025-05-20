<?php
namespace Magecomp\Whatsapppro\Block\Customer;

class Register extends \Magento\Framework\View\Element\Template
{
    protected $helpercustomer;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Whatsapppro\Helper\Data $helpercustomer,
        array $data = []
    ) {
        $this->helpercustomer = $helpercustomer;
        parent::__construct($context, $data);
    }

    public function getDefaultCountry()
    {
        return $this->helpercustomer->getDefaultCountry();
    }
    public function getGeoCountryCode()
    {
        return $this->helpercustomer->getGeoCountryCode();
    }
    public function getCountryFlagEnable()
    {
        return $this->helpercustomer->getCountryFlagEnable();
    }
    public function getCountryFlagDetectByIP()
    {
        return $this->helpercustomer->getCountryFlagDetectByIp();
    }
}
