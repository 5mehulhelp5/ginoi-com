<?php

namespace Magebees\ImageFlipper\Block;

class ImageFlipper extends \Magento\Framework\View\Element\Template
{
    protected $_config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        parent::__construct($context);
    }

    /* For get the configuration value of default extension settings*/
    public function getConfig()
    {
        return $this->_scopeConfig->getValue('imageflipper/setting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
