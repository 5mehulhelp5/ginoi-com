<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier as ShippingAbstractCarrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackingResultFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Meetanshi\CustomCarrierTracking\Helper\Data;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Tracking\Result;
use Meetanshi\CustomCarrierTracking\Model\CarrierFactory;

/**
 * Class AbstractCarrier
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
abstract class AbstractCarrier extends ShippingAbstractCarrier
{
    /**
     * @var ResultFactory
     */
    protected $rateFactory;
    /**
     * @var TrackingResultFactory
     */
    protected $trackFactory;
    /**
     * @var StatusFactory
     */
    protected $trackStatusFactory;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;


    /**
     * AbstractCarrier constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateFactory
     * @param TrackingResultFactory $trackFactory
     * @param StatusFactory $trackStatusFactory
     * @param Data $helper
     * @param CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(ScopeConfigInterface $scopeConfig, ErrorFactory $rateErrorFactory, LoggerInterface $logger, ResultFactory $rateFactory, TrackingResultFactory $trackFactory, StatusFactory $trackStatusFactory, Data $helper, CarrierFactory $carrierFactory, array $data = [])
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateFactory = $rateFactory;
        $this->trackFactory = $trackFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->helper = $helper;
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|null
     */
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        if ($this->getConfigFlag('active') && $this->helper->isEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @param $tracking
     * @return bool|mixed
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * @param $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->generateTracking($trackings);

        return $this->_result;
    }

    /**
     *
     */
    protected function setTrackingReqeust()
    {
        $object = new \Magento\Framework\DataObject();

        $userId = $this->getConfigData('userid');
        $object->setUserId($userId);

        $this->_rawTrackRequest = $object;
    }

    /** Popup window to tracker **/
    protected function generateTracking($trackings)
    {
        $this->_result = $this->trackFactory->create();
        foreach ($trackings as $tracking) {
            $status = $this->trackStatusFactory->create();

            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);

            $status->setUrl($this->helper->prepareTrackingLink($this, $tracking));

            $this->_result->append($status);
        }
    }
}
