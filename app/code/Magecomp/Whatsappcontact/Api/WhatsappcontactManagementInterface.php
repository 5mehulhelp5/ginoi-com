<?php

namespace Magecomp\Whatsappcontact\Api;

interface WhatsappcontactManagementInterface
{
    /**
     * GET config data
     * @param int $storeid
     * @return string
     */
    public function getConfigData($storeid);

}
