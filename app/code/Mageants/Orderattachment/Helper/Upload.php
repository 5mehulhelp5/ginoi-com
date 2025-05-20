<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\ScopeInterface;
use Mageants\Orderattachment\Model\Attachment;

class Upload
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $fileSystem
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UploaderFactory $uploaderFactory,
        Filesystem $fileSystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileSystem = $fileSystem;
    }

    /**
     * File Upload Data
     *
     * @param  array $uploadData
     * @return array
     */
    public function uploadFileAndGetInfo($uploadData)
    {
        $allowedExtensions = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
            ScopeInterface::SCOPE_STORE
        );
        $varDirectoryPath = $this->fileSystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath("orderattachment");

        $result = $this->uploaderFactory
            ->create(['fileId' => $uploadData])
            ->setAllowedExtensions(explode(',', $allowedExtensions))
            ->setAllowRenameFiles(true)
            ->setFilesDispersion(true)
            ->save($varDirectoryPath);

        return $result;
    }
}
