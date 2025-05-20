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

namespace Mageplaza\GiftCard\Api\Data;

/**
 * Interface CheckCodeInterface
 * @api
 */
interface CheckCodeInterface
{
    const BALANCE              = 'balance';
    const BALANCE_FORMATTED    = 'balance_formatted';
    const STATUS               = 'status';
    const STATUS_LABEL         = 'status_label';
    const EXPIRED_AT           = 'expired_at';
    const EXPIRED_AT_FORMATTED = 'expired_at_formatted';

    /**
     * @return float
     */
    public function getBalance();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setBalance($value);

    /**
     * @return string
     */
    public function getBalanceFormatted();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBalanceFormatted($value);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getStatusLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStatusLabel($value);

    /**
     * @return string
     */
    public function getExpiredAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExpiredAt($value);

    /**
     * @return string
     */
    public function getExpiredAtFormatted();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExpiredAtFormatted($value);
}
