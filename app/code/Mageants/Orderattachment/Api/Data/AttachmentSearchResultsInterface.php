<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface AttachmentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Items
     *
     * @return void
     */
    public function getItems();

    /**
     * Set Items
     *
     * @param array $items
     * @return void
     */
    public function setItems(array $items);
}
