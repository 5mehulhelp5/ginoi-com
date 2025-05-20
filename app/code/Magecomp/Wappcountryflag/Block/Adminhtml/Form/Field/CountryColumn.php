<?php
namespace Magecomp\Wappcountryflag\Block\Adminhtml\Form\Field;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CountryColumn extends Select
{
    protected $countryHelper;
    protected $_options;

    public function __construct(
        Context $context,
        Collection $countryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countryHelper = $countryHelper;
    }
    
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getAllowedCountries());
        }
        return parent::_toHtml();
    }
    public function getAllowedCountries()
    {
        $foregroundCountries = '';
        if (!$this->_options) {
            $this->_options = $this->countryHelper->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }
        $options = $this->_options;


        array_unshift($options, ['value'=> '1', 'label'=>__('Default')]);
        return $options;
    }
}
