<?php

namespace Magecomp\Whatsappcontact\Block;

use Magecomp\Whatsappcontact\Helper\Data as WhatsappHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Whatsappcontact extends Template
{

    protected $_modelWhatsappcontactproFactory;
    protected $whatsappHelper;

    public function __construct(
        Context $context,
        WhatsappHelper $whatsappHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->whatsappHelper = $whatsappHelper;
    }

    public function getWhatsappData()
    {
        return $this->whatsappHelper->getData();
    }
}
