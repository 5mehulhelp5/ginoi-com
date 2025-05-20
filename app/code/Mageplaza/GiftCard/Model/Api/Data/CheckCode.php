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

namespace Mageplaza\GiftCard\Model\Api\Data;

use Magento\Framework\Model\AbstractExtensibleModel;
use Mageplaza\GiftCard\Api\Data\CheckCodeInterface;

/**
 * Class CheckCode
 * @package Mageplaza\GiftCard\Model\Api\Data
 */
class CheckCode extends AbstractExtensibleModel implements CheckCodeInterface
{
    /**
     * @inheritDoc
     */
    public function getBalance()
    {
        return $this->getData(self::BALANCE);
    }

    /**
     * @inheritDoc
     */
    public function setBalance($value)
    {
        return $this->setData(self::BALANCE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getExpiredAt()
    {
        return $this->getData(self::EXPIRED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setExpiredAt($value)
    {
        return $this->setData(self::EXPIRED_AT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getExpiredAtFormatted()
    {
        return $this->getData(self::EXPIRED_AT_FORMATTED);
    }

    /**
     * @inheritDoc
     */
    public function setExpiredAtFormatted($value)
    {
        return $this->setData(self::EXPIRED_AT_FORMATTED, $value);
    }

    /**
     * @inheritDoc
     */
    public function getBalanceFormatted()
    {
        return $this->getData(self::BALANCE_FORMATTED);
    }

    /**
     * @inheritDoc
     */
    public function setBalanceFormatted($value)
    {
        return $this->setData(self::BALANCE_FORMATTED, $value);
    }

    /**
     * @inheritDoc
     */
    public function getStatusLabel()
    {
        return $this->getData(self::STATUS_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setStatusLabel($value)
    {
        return $this->setData(self::STATUS_LABEL, $value);
    }
}
