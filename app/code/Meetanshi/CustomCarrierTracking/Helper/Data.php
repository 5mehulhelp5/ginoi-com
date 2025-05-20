<?php

namespace Meetanshi\CustomCarrierTracking\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\InfoFactory;
use Magento\Store\Model\ScopeInterface;
use Meetanshi\CustomCarrierTracking\Model\ResourceModel\Carrier\CollectionFactory;
use Meetanshi\CustomCarrierTracking\Model\CarrierFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Shipping\Model\Config;

/**
 * Class Data
 * @package Meetanshi\CustomCarrierTracking\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;
    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;
    /**
     * @var InfoFactory
     */
    protected $shippingInfoFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Config
     */
    protected $shippingConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param InfoFactory $shippingInfoFactory
     * @param CollectionFactory $collectionFactory
     * @param CarrierFactory $carrierFactory
     * @param TrackFactory $trackFactory
     * @param OrderFactory $orderFactory
     * @param Config $shippingConfig
     */
    public function __construct(Context $context, Registry $registry, StoreManagerInterface $storeManager, TimezoneInterface $localeDate, ShipmentRepositoryInterface $shipmentRepository, InfoFactory $shippingInfoFactory, CollectionFactory $collectionFactory, CarrierFactory $carrierFactory, TrackFactory $trackFactory, OrderFactory $orderFactory, Config $shippingConfig)
    {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->shipmentRepository = $shipmentRepository;
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->collectionFactory = $collectionFactory;
        $this->carrierFactory = $carrierFactory;
        $this->trackFactory = $trackFactory;
        $this->orderFactory = $orderFactory;
        $this->shippingConfig = $shippingConfig;
        parent::__construct($context);
    }

    /**
     * @param $tracking
     * @param $config
     * @param $trackingCode
     * @return string|string[]|null
     */
    public function prepareTrackingLink($config, $trackingCode)
    {
        $order_id = $firstname = $lastname = $countryCode = $postCode = '';

        $trackingData = $this->trackFactory->create()->load($trackingCode, 'track_number');
        $order = $this->orderFactory->create()->load($trackingData->getOrderId());
        $carrier = $this->carrierFactory->create()->load($config->getConfigData('url'));
        $trackingDate = $this->localeDate->scopeDate();

        if ($shipAddress = $order->getShippingAddress()) {
            $firstname = $shipAddress->getFirstname();
            $lastname = $shipAddress->getLastname();
            $countryCode = $shipAddress->getCountryId();
            $postCode = $shipAddress->getPostcode();
        }
        if ($trackingData->getCreatedAt()) {
            $trackingDate = $this->localeDate->scopeDate(
                $order->getStore(),
                $trackingData->getCreatedAt(),
                true
            );
        }

        return preg_replace(
            [
                "/#TRACKINGCODE#/i",
                "/#POSTCODE#/i",
                "/#ORDERID#/i",
                "/#FIRSTNAME#/i",
                "/#LASTNAME#/i",
                "/#COUNTRYCODE#/i",
                "/#d#/i",
                "/#m#/i",
                "/#y#/",
                "/#Y#/"
            ],
            [
                urlencode($trackingCode),
                urlencode($postCode),
                urlencode($order_id),
                urlencode($firstname),
                urlencode($lastname),
                urlencode($countryCode),
                $trackingDate->format('j'),
                $trackingDate->format('n'),
                $trackingDate->format('Y'),
                $trackingDate->format('y')
            ],
            $carrier->getUrl()
        );
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue('customcarriertracking/general/enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $model
     * @return string
     */
    public function prepareTrackingUrl($model)
    {
        $fullUrl = "";
        $carrierInstances = $this->shippingConfig->getAllCarriers();
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                if ($carrier->getConfigData('title') == $model['title']) {
                    $manualUrl = $carrier->getConfigData('url');

                    $order_id = $firstname = $lastname = $countryCode = $postCode = '';

                    $trackingData = $this->trackFactory->create()->load($model['track_number'], 'track_number');
                    $order = $this->orderFactory->create()->load($trackingData->getOrderId());
                    $carrier = $this->carrierFactory->create()->load($manualUrl);
                    $trackingDate = $this->localeDate->scopeDate();

                    if ($shipAddress = $order->getShippingAddress()) {
                        $firstname = $shipAddress->getFirstname();
                        $lastname = $shipAddress->getLastname();
                        $countryCode = $shipAddress->getCountryId();
                        $postCode = $shipAddress->getPostcode();
                    }
                    if ($trackingData->getCreatedAt()) {
                        $trackingDate = $this->localeDate->scopeDate(
                            $order->getStore(),
                            $trackingData->getCreatedAt(),
                            true
                        );
                    }

                    return preg_replace(
                        [
                            "/#TRACKINGCODE#/i",
                            "/#POSTCODE#/i",
                            "/#ORDERID#/i",
                            "/#FIRSTNAME#/i",
                            "/#LASTNAME#/i",
                            "/#COUNTRYCODE#/i",
                            "/#d#/i",
                            "/#m#/i",
                            "/#y#/",
                            "/#Y#/"
                        ],
                        [
                            urlencode($model['track_number']),
                            urlencode($postCode),
                            urlencode($order_id),
                            urlencode($firstname),
                            urlencode($lastname),
                            urlencode($countryCode),
                            $trackingDate->format('j'),
                            $trackingDate->format('n'),
                            $trackingDate->format('Y'),
                            $trackingDate->format('y')
                        ],
                        $carrier->getUrl()
                    );
                }
            }
        }
        return $fullUrl;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return [
            '0' => __('Inactive'),
            '1' => __('Active'),
        ];
    }

    /**
     * @return \Meetanshi\CustomCarrierTracking\Model\ResourceModel\Carrier\Collection
     */
    public function getActiveCarriers()
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('active', ['eq' => 1]);
        return $collection;
    }
    public function logMsg($msg){
        $this->_logger->info($msg);
    }
}
