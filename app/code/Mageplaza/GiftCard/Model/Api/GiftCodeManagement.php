<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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

namespace Mageplaza\GiftCard\Model\Api;

use Exception;
use IntlDateFormatter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\GiftCard\Api\Data\CheckCodeInterface;
use Mageplaza\GiftCard\Api\Data\CheckCodeInterfaceFactory;
use Mageplaza\GiftCard\Api\Data\GiftCodeInterface;
use Mageplaza\GiftCard\Api\Data\GiftCodeSearchResultInterfaceFactory;
use Mageplaza\GiftCard\Api\Data\TemplateFieldsInterfaceFactory;
use Mageplaza\GiftCard\Api\GiftCodeManagementInterface;
use Mageplaza\GiftCard\Helper\Data;
use Mageplaza\GiftCard\Model\GiftCard;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard\Collection;

/**
 * Class GiftCodeManagement
 * @package Mageplaza\GiftCard\Model\Api
 */
class GiftCodeManagement extends AbstractManagement implements GiftCodeManagementInterface
{
    /**
     * @var GiftCardFactory
     */
    private $giftCardFactory;

    /**
     * @var GiftCodeSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CheckCodeInterfaceFactory
     */
    private $checkCodeInterfaceFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * GiftCodeManagement constructor.
     *
     * @param TemplateFieldsInterfaceFactory $templateFieldsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param GiftCodeSearchResultInterfaceFactory $searchResultFactory
     * @param GiftCardFactory $giftCardFactory
     * @param CheckCodeInterfaceFactory $checkCodeInterfaceFactory
     * @param Data $helperData
     */
    public function __construct(
        TemplateFieldsInterfaceFactory $templateFieldsFactory,
        CollectionProcessorInterface $collectionProcessor,
        GiftCodeSearchResultInterfaceFactory $searchResultFactory,
        GiftCardFactory $giftCardFactory,
        CheckCodeInterfaceFactory $checkCodeInterfaceFactory,
        Data $helperData
    ) {
        $this->giftCardFactory           = $giftCardFactory;
        $this->searchResultFactory       = $searchResultFactory;
        $this->checkCodeInterfaceFactory = $checkCodeInterfaceFactory;
        $this->helperData                = $helperData;

        parent::__construct($templateFieldsFactory, $collectionProcessor);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $searchResult */
        $searchResult = $this->searchResultFactory->create();

        return $this->getListEntity($searchResult, $searchCriteria);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->getEntity($this->giftCardFactory->create(), $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->deleteEntity($this->giftCardFactory->create(), $id);
    }

    /**
     * {@inheritdoc}
     * @param GiftCodeInterface|GiftCard $entity
     */
    public function save(GiftCodeInterface $entity)
    {
        return $this->saveEntity($entity);
    }

    /**
     * @param string $code
     *
     * @return CheckCodeInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function check($code)
    {
        $giftCard = $this->giftCardFactory->create()->loadByCode($code);

        $this->helperData->validateMaxWrongTime($giftCard, true);

        $giftCard->isActive();

        return $this->checkCodeInterfaceFactory->create([
            'data' => [
                'status'               => $giftCard->getStatus(),
                'status_label'         => $giftCard->getStatusLabel(),
                'balance'              => $this->helperData->convertPrice($giftCard->getBalance(), false, false),
                'balance_formatted'    => $this->helperData->convertPrice($giftCard->getBalance()),
                'expired_at'           => $giftCard->getExpiredAt(),
                'expired_at_formatted' => $giftCard->getExpiredAt()
                    ? $this->helperData->formatDate($giftCard->getExpiredAt(), IntlDateFormatter::MEDIUM)
                    : __('Permanent')
            ]
        ]);
    }
}
