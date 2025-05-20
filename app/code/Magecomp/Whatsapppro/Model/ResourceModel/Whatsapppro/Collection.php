<?php
namespace Magecomp\Whatsapppro\Model\ResourceModel\Whatsapppro;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init("Magecomp\Whatsapppro\Model\Whatsapppro", "Magecomp\Whatsapppro\Model\ResourceModel\Whatsapppro");
    }
}
