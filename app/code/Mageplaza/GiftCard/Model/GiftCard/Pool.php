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

namespace Mageplaza\GiftCard\Model\GiftCard;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Mageplaza\GiftCard\Model\PoolFactory;

/**
 * Class Pool
 * @package Mageplaza\GiftCard\Model\GiftCard
 */
class Pool extends AbstractSource
{
    /**
     * @var PoolFactory
     */
    protected $poolFactory;

    /**
     * @var array
     */
    protected $pool;

    /**
     * Pool constructor.
     *
     * @param PoolFactory $poolFactory
     */
    public function __construct(PoolFactory $poolFactory)
    {
        $this->poolFactory = $poolFactory;
    }

    /**
     * Get all option
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->pool === null) {
            $this->pool = [];
            $collection = $this->poolFactory->create()->getCollection();
            foreach ($collection as $pool) {
                $this->pool[] = ['value' => $pool->getId(), 'label' => $pool->getName()];
            }
        }

        return $this->pool;
    }
}
