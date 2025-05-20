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

namespace Mageplaza\GiftCard\Plugin\Model\Price;

use Closure;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Row;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class Product
 * @package Mageplaza\GiftCard\Plugin\Model\Catalog
 */
class RowIndexer
{
    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductRepository $productRepository
    ) {
        $this->_productRepository = $productRepository;
    }

    /**
     * @param Row $subject
     * @param Closure $proceed
     * @param $id
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    public function aroundExecute(Row $subject, Closure $proceed, $id)
    {
        if (!empty($id)) {
            $product = $this->_productRepository->getById($id);
            if ($product->getTypeId() === GiftCard::TYPE_GIFTCARD) {
                return false;
            }
        }

        return $proceed($id);
    }
}
