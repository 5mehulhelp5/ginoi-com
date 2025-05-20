<?php
/**
 * Copyright © Boolfly. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Boolfly\Wishlist\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic as GenericActions;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class Actions extends GenericActions
{
    private WishlistFactory $wishlistFactory;
    protected ItemFactory $wishlistItemFactory;
    private CustomerSession $customerSession;
    private SerializerInterface $serializer;

    private UrlInterface $urlBuilder;

    private const string ADD_WISHLIST_URL = 'wishlist/cart/add';
    private const string REMOVE_WISHLIST_URL = 'wishlist/index/remove';

    /**
     * @param Context $context
     * @param WishlistFactory $wishlistFactory
     * @param ItemFactory $wishlistItemFactory
     * @param CustomerSession $customerSession
     * @param SerializerInterface $serializer
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        WishlistFactory $wishlistFactory,
        ItemFactory $wishlistItemFactory,
        CustomerSession $customerSession,
        SerializerInterface $serializer,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        array $data = []
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->serializer = $serializer;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @param $productId
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getWishlistItemId($productId): ?int
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return null;
        }

        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
        $wishlistItems = $wishlist->getItemCollection();

        foreach ($wishlistItems as $item) {
            if ($item->getProductId() == $productId) {
                return $item->getId(); // Return wishlist item ID if exists
            }
        }
        return null;
    }

    /**
     * Get post parameters for adding/removing from wishlist
     *
     * @param AbstractItem $item
     * @return string
     * @throws NoSuchEntityException
     */
    public function getWishlistPostJson(AbstractItem $item): string
    {
        $productId = $item->getProduct()->getId();
        $wishlistItemId = $this->getWishlistItemId($productId);
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            // If user is not logged in, always generate "Add to Wishlist" URL
            $url = $this->urlBuilder->getUrl(self::ADD_WISHLIST_URL);
            $data = ['product' => $productId];
        } else {
            if ($wishlistItemId) {
                // Generate remove from wishlist URL
                $url = $this->urlBuilder->getUrl(self::REMOVE_WISHLIST_URL);
                $data = ['item' => $wishlistItemId];
            } else {
                // Generate add to wishlist URL
                $url = $this->urlBuilder->getUrl(self::ADD_WISHLIST_URL);
                $data = ['product' => $productId];
            }
        }

        return $this->serializer->serialize(['action' => $url, 'data' => $data]);
    }
}
