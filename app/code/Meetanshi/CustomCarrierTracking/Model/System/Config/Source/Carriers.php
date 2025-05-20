<?php

namespace Meetanshi\CustomCarrierTracking\Model\System\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\ScopeInterface;
use Meetanshi\CustomCarrierTracking\Helper\Data;

/**
 * Class Carriers
 * @package Meetanshi\CustomCarrierTracking\Model\System\Config\Source
 */
class Carriers implements ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Carriers constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Config $config, StoreManagerInterface $storeManager, Data $helper)
    {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray()
    {
        $carriers = [];
        $carriers[] = ['value' => '', 'label' => __('Please Select Carrier')];
        $collection = $this->helper->getActiveCarriers();
        if (sizeof($collection) > 0) {
            foreach ($this->helper->getActiveCarriers() as $carrierConfig) {
                $carriers[] = [
                    'value' => $carrierConfig->getId(),
                    'label' => $carrierConfig->getTitle()
                ];
            }
        }

        return $carriers;
    }
}
