<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Controller\Template;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaStorage\Model\File\Uploader;
use Mageplaza\GiftCard\Helper\Data;
use Mageplaza\GiftCard\Helper\Template;

/**
 * Class Upload
 * @package Mageplaza\GiftCard\Controller\Template
 */
class Upload extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Template
     */
    protected $_templateHelper;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Data $helperData
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->_templateHelper  = $helperData->getTemplateHelper();
        $this->helperData       = $helperData;

        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute()
    {
        try {
            /** @var Uploader $uploader */
            $uploader = $this->_objectManager->create(
                Uploader::class,
                ['fileId' => 'image']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $maxFileSize = $this->helperData->getMaxFileSize();
            if ($uploader->getFileSize() > $maxFileSize * 1024 * 1024) {
                return $this->getRawResponse([
                    'error'         => 1,
                    'error_message' => __('File you are trying to upload exceeds maximum file size limit.')
                ]);
            }

            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            /** @var Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get(Filesystem::class)
                ->getDirectoryRead(DirectoryList::MEDIA);

            $result = $uploader->save($mediaDirectory->getAbsolutePath($this->_templateHelper->getBaseTmpMediaPath()));

            unset($result['tmp_name'], $result['path']);

            $result['url']  = $this->_templateHelper->getTmpMediaUrl($result['file']);
            $result['file'] .= '.tmp';
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(Template::jsonEncode($result));

        return $response;
    }

    /**
     * @param array $result
     *
     * @return Raw
     */
    public function getRawResponse($result)
    {
        /** @var Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(Template::jsonEncode($result));

        return $response;
    }
}
