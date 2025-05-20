<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Model\ResourceModel\Attachment;

use Mageants\Orderattachment\Model\Attachment;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Attachment::class,
            \Mageants\Orderattachment\Model\ResourceModel\Attachment::class
        );
    }
}
