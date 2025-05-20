<?php
namespace Magecomp\Whatsapppro\Model;

use Magento\Framework\Model\AbstractModel;

class Whatsapppro extends AbstractModel
{
    protected function _construct()
    {
        $this->_init("Magecomp\Whatsapppro\Model\ResourceModel\Whatsapppro");
    }
}
