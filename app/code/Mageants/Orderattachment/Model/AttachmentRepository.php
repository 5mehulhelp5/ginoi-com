<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Model;

use Mageants\Orderattachment\Api\Data;
use Mageants\Orderattachment\Api\AttachmentRepositoryInterface;
use Mageants\Orderattachment\Api\Data\AttachmentInterface;
use Mageants\Orderattachment\Api\Data\AttachmentInterfaceFactory;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Mageants\Orderattachment\Model\ResourceModel\Attachment as ResourceAttachment;
use Mageants\Orderattachment\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var ResourceAttachment
     */
    protected $resource;

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $attachmentCollectionFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var BlockInterfaceFactory
     */
    protected $dataAttachmentFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param ResourceBlock $resource
     * @param BlockFactory $attachmentFactory
     * @param Data\BlockInterfaceFactory $dataAttachmentFactory
     * @param BlockCollectionFactory $attachmentCollectionFactory
     * @param Data\BlockSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceAttachment $resource,
        AttachmentFactory $attachmentFactory,
        AttachmentInterfaceFactory $dataAttachmentFactory,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        Data\AttachmentSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->blockFactory = $attachmentFactory;
        $this->blockCollectionFactory = $attachmentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBlockFactory = $dataAttachmentFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }
    /**
     * Save function
     *
     * @param AttachmentInterface $attachment
     * @return void
     */
    public function save(AttachmentInterface $attachment)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attachment->setStoreId($storeId);
        try {
            $this->resource->save($attachment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $attachment;
    }

    /**
     * Get ById function
     *
     * @param mixed $attachmentId
     * @return void
     */
    public function getById($attachmentId)
    {
        $attachment = $this->blockFactory->create();
        $this->resource->load($attachment, $attachmentId);
        if (!$attachment->getId()) {
            throw new NoSuchEntityException(__('CMS Block with id "%1" does not exist.', $attachmentId));
        }
        return $attachment;
    }
    /**
     * Get List function
     *
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->blockCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $blocks = [];
        /** @var Block $blockModel */
        foreach ($collection as $blockModel) {
            $blockData = $this->dataBlockFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $blockData,
                $blockModel->getData(),
                AttachmentInterface::class
            );
            $blocks[] = $this->dataObjectProcessor->buildOutputDataArray(
                $blockData,
                AttachmentInterface::class
            );
        }
        $searchResults->setItems($blocks);
        return $searchResults;
    }

    /**
     * Delete Function
     *
     * @param AttachmentInterface $attachment
     * @return void
     */
    public function delete(AttachmentInterface $attachment)
    {
        try {
            $this->resource->delete($attachment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete By Id function
     *
     * @param mixed $attachmentId
     * @return void
     */
    public function deleteById($attachmentId)
    {
        return $this->delete($this->getById($attachmentId));
    }
}
