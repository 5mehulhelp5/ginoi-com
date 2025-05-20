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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Plugin\Export;

use Magento\Framework\App\Request\Http;
use Magento\Ui\Model\Export\MetadataProvider as BaseMetadataProvider;
use Mageplaza\GiftCard\Model\GiftCard\Status;
use Mageplaza\GiftCard\Model\GiftCardFactory;

/**
 * Class MetadataProvider
 * @package Mageplaza\GiftCard\Plugin\Export
 */
class MetadataProvider
{
    /**
     * @var Http
     */
    protected Http $_request;

    /**
     * @var GiftCardFactory
     */
    protected $giftCardFactory;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request,
        GiftCardFactory $giftCardFactory
    ) {
        $this->_request        = $request;
        $this->giftCardFactory = $giftCardFactory;
    }

    /**
     * @param BaseMetadataProvider $subject
     * @param callable $proceed
     * @param $document
     * @param $fields
     * @param $options
     * @return array
     */
    public function aroundGetRowData(BaseMetadataProvider $subject, callable $proceed, $document, $fields, $options): array
    {
        $nameSpace = $this->_request->getParam('namespace');
        if ($nameSpace === 'giftcard_code_listing' || $nameSpace === 'giftcard_pool_listing') {
            $row = [];
            foreach ($fields as $column) {
                if (isset($options[$column])) {
                    $key = $document->getCustomAttribute($column)->getValue();
                    if (isset($options[$column][$key])) {
                        if ($column === 'store_id') {
                            $row[] = $key;
                        } else {
                            $row[] = $options[$column][$key];
                        }
                    } else {
                        $row[] = $key;
                    }
                } else {
                    if ($nameSpace === 'giftcard_pool_listing' && $column === 'available') {
                        $collection = $this->giftCardFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('pool_id', $document->getPoolId());

                        $totalSize = $collection->getSize();
                        $collection->resetTotalRecords();

                        $collection->addFieldToFilter('status', Status::STATUS_ACTIVE);
                        $activeSize = $collection->count();

                        $row[] = $activeSize . "/" . $totalSize;
                    }
                    $row[] = $document->getCustomAttribute($column)->getValue();
                }
            }

            return $row;
        }
        return $proceed($document, $fields, $options);
    }
}
