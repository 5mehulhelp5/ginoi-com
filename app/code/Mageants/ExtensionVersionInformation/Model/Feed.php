<?php

namespace Mageants\ExtensionVersionInformation\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Backend\App\ConfigInterface;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class Feed
 * @package Mageants\ExtensionVersionInformation\Model
 */
class Feed extends AbstractModel
{
    /**
     * @var InboxFactory
     */
    protected $_inboxFactory;
    /**
     * @var CurlFactory
     */
    protected $curlFactory;
    /**
     * @var DeploymentConfig
     */
    protected $_deploymentConfig;
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Feed constructor.
     * @param Context $context
     * @param Registry $registry
     * @param InboxFactory $inboxFactory
     * @param CurlFactory $curlFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_inboxFactory = $inboxFactory;
        $this->curlFactory = $curlFactory;
        $this->_deploymentConfig = $deploymentConfig;
        $this->productMetadata = $productMetadata;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return bool|string
     */
    public function getFeedUrl()
    {
        return base64_decode('aHR0cHM6Ly93d3cubWFnZWFudHMuY29tL25vdGlmaWNhdGlvbi54bWw=');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function checkUpdate()
    {
        $version = $this->productMetadata->getVersion();
        if ($this->getFrequency() + $this->getLastUpdate() > time()) {
            return $this;
        }
        $feedData = [];
        $feedXml = $this->getFeedData();
        $installDate = strtotime($this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $versionnotification = $item->version;
                $itemPublicationDate = strtotime((string)$item->pubDate);
                // if ($version <= $versionnotification) {
                if ($installDate <= $itemPublicationDate) {
                    $feedData[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => $this->escapeString($item->title),
                        'description' => $this->escapeString($item->description),
                        'url' => $this->escapeString($item->link),
                    ];
                }
            }
            if ($feedData) {
                $this->_inboxFactory->create()->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();
        return $this;
    }

    /**
     * @return string
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('notifications_lastcheck');
    }

    /**
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'notifications_lastcheck');
        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return 3600;
    }

    /**
     * @return bool|\SimpleXMLElement
     */
    public function getFeedData()
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout' => 2,
                'useragent' => $this->productMetadata->getName()
                    . '/' . $this->productMetadata->getVersion()
                    . ' (' . $this->productMetadata->getEdition() . ')',
                'referer' => $this->urlBuilder->getUrl('*/*/*')
            ]
        );
        $curl->write('GET', $this->getFeedUrl(), '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();
        try {
            $xml = new \SimpleXMLElement($data);
        } catch (\Exception $e) {
            return false;
        }

        return $xml;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getFeedXml()
    {
        try {
            $data = $this->getFeedData();
            $xml = new \SimpleXMLElement($data);
        } catch (\Exception $e) {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
        }

        return $xml;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return string
     */
    private function escapeString(\SimpleXMLElement $data)
    {
        return htmlspecialchars((string)$data);
    }
}
