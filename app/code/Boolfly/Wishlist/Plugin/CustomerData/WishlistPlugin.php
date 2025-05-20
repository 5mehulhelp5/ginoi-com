<?php
/**
 * Copyright © Boolfly. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Boolfly\Wishlist\Plugin\CustomerData;

use Magento\Wishlist\Helper\Data as HelperData;

/**
 * Class WishlistPlugin
 *
 * @package Boolfly\AjaxWishlist\Plugin\CustomerData
 */
class WishlistPlugin
{

    /**
     * @var HelperData
     */
    private $wishlistHelper;

    /**
     * WishlistPlugin constructor.
     *
     * @param HelperData $wishlistHelper
     */
    public function __construct(
        HelperData $wishlistHelper
    ) {
        $this->wishlistHelper = $wishlistHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return array
     */
    public function afterGetSectionData($subject, $result)
    {
        if (is_array($result)) {
            $result['wishlist_ids'] = $this->getItemIds();
        }

        return $result;
    }

    /**
     * Get wishlist item ids
     *
     * @return array
     */
    protected function getItemIds()
    {
        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setInStockFilter(true)->setOrder('added_at');

        return $collection->getAllIds();
    }
}
