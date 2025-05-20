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

namespace Mageplaza\GiftCard\Plugin\Model\Cart;

use Closure;
use Magento\Checkout\Model\Cart\ImageProvider as ImageBase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\GiftCard\Helper\Media;
use Magento\Quote\Api\CartItemRepositoryInterface;

/**
 * Class ImageProvider
 * @package Mageplaza\GiftCard\Plugin\CustomerData\Checkout
 */
class ImageProvider
{
    /**
     * @var Media
     */
    private $mediaHelper;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @param Media $mediaHelper
     * @param CartItemRepositoryInterface $itemRepository
     */
    public function __construct(
        Media $mediaHelper,
        CartItemRepositoryInterface $itemRepository
    ) {
        $this->mediaHelper    = $mediaHelper;
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param ImageBase $subject
     * @param Closure $proceed
     * @param $cartId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundGetImages(ImageBase $subject, Closure $proceed, $cartId)
    {
        $result = $proceed($cartId);
        $items  = $this->itemRepository->getList($cartId);

        /** @var Item $cartItem */
        foreach ($items as $cartItem) {
            $image = $cartItem->getOptionByCode('image');

            if (!$image) {
                continue;
            }

            $url = $this->mediaHelper->getGiftCardImageProduct($cartItem, $image->getValue());
            if ($url && isset($result[$cartItem->getItemId()])) {
                $result[$cartItem->getItemId()]['src'] = $url;
            }
        }

        return $result;
    }
}
