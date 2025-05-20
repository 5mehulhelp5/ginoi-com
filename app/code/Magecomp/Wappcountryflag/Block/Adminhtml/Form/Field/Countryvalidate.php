<?php
namespace Magecomp\Wappcountryflag\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Countryvalidate extends AbstractFieldArray
{
    private $taxRenderer;
    private $stateRenderer;
   
    protected function _prepareToRender()
    {
        $this->addColumn('digit', ['label' => __('Digit'), 'class' => 'required-entry validate-number validate-not-negative-number validate-digits-range digits-range-0-15']);
        
        $this->addColumn('country', [
            'label' => __('Country'),
            'renderer' => $this->getStateRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
         $options = [];

        $tax = $row->getTax();
        if ($tax !== null) {
            $options['option_' . $this->getStateRenderer()->calcOptionHash($tax)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getStateRenderer()
    {
        if (!$this->stateRenderer) {
            $this->stateRenderer = $this->getLayout()->createBlock(
                \Magecomp\Wappcountryflag\Block\Adminhtml\Form\Field\CountryColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->stateRenderer;
    }
}
