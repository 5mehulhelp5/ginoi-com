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

namespace Mageplaza\GiftCard\Plugin\Controller;

use Closure;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\ImportExport\Controller\Adminhtml\Import\Download;

/**
 * Download sample file controller
 */
class ImportDownloadSample
{
    /**
     * Import file name
     */
    const IMPORT_FILE = 'gift_card';
    /**
     * Module name
     */
    const SAMPLE_FILES_MODULE = 'Mageplaza_GiftCard';

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param Http $request
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ReadFactory $readFactory,
        ComponentRegistrar $componentRegistrar,
        Http $request
    ) {
        $this->fileFactory           = $fileFactory;
        $this->resultRawFactory      = $resultRawFactory;
        $this->readFactory           = $readFactory;
        $this->componentRegistrar    = $componentRegistrar;
        $this->request               = $request;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager        = $context->getMessageManager();
    }

    /**
     * @param Download $download
     * @param Closure $proceed
     *
     * @return Redirect|Raw
     * @throws Exception
     */
    public function aroundExecute(Download $download, Closure $proceed)
    {
        if ($this->request->getParam('filename') !== self::IMPORT_FILE) {
            return $proceed();
        }

        $fileName         = $this->request->getParam('filename') . '.csv';
        $moduleDir        = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE);
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead    = $this->readFactory->create($moduleDir);
        $filePath         = $directoryRead->getRelativePath($fileAbsolutePath);

        try {
            if (!$directoryRead->isFile($filePath)) {
                /* @var Redirect $resultRedirect */
                $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/import');

                return $resultRedirect;
            }
            $fileContents = $directoryRead->readFile($filePath);

        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));

            return $this->getResultRedirect();
        }

        $fileSize = isset($directoryRead->stat($filePath)['size']) ? $directoryRead->stat($filePath)['size'] : null;

        return $this->fileFactory->create(
            $fileName,
            $fileContents,
            DirectoryList::VAR_IMPORT_EXPORT,
            'application/octet-stream',
            $fileSize
        );
    }
}
