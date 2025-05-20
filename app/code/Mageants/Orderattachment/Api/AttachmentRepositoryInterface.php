<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AttachmentRepositoryInterface
{

    /**
     * Save function
     *
     * @param Data\AttachmentInterface $attachment
     * @return void
     */
    public function save(Data\AttachmentInterface $attachment);

    /**
     * GetById function
     *
     * @param int $attachmentId
     * @return void
     */
    public function getById($attachmentId);

    /**
     * GetList function
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return void
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete function
     *
     * @param Data\AttachmentInterface $attachment
     * @return void
     */
    public function delete(Data\AttachmentInterface $attachment);

    /**
     * DeleteById function
     *
     * @param mixed $attachmentId
     * @return void
     */
    public function deleteById($attachmentId);
}
