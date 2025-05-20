<?php

namespace Meetanshi\CustomCarrierTracking\Plugin;

use Meetanshi\CustomCarrierTracking\Helper\Data;
use Magento\SalesGraphQl\Model\Resolver\Shipment\ShipmentTracking as MainShipmentTracking;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ShipmentTracking
 * @package Meetanshi\CustomCarrierTracking\Plugin
 */
class ShipmentTracking
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * ShipmentTracking constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }


    /**
     * @param MainShipmentTracking $subject
     * @param \Closure $proceed
     * @param $field
     * @param $context
     * @param $info
     * @param $value
     * @param $args
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundResolve(
        MainShipmentTracking $subject,
        \Closure $proceed,
        $field,
        $context,
        $info,
        $value,
        $args
    )
    {
        if ($this->helper->isEnabled()) {
            if (!isset($value['model']) && !($value['model'] instanceof ShipmentInterface)) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            /** @var ShipmentInterface $shipment */
            $shipment = $value['model'];
            $tracks = $shipment->getTracks();

            $shipmentTracking = [];
            foreach ($tracks as $tracking) {
                $shipmentTracking[] = [
                    'title' => $tracking->getTitle(),
                    'carrier' => $tracking->getCarrierCode(),
                    'number' => $tracking->getTrackNumber(),
                    'trackingurl' => $this->helper->graphQLprepareTrackingLink($tracking->getCarrierCode(), $tracking->getTrackNumber()),
                    'model' => $tracking
                ];
            }
            return $shipmentTracking;
        }
        return $proceed($field, $context, $info, $value, $args);
    }
}
