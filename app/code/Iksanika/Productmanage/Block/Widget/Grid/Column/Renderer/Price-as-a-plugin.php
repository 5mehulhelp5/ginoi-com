<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer currency
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Price
{

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
//    public function render(\Magento\Framework\DataObject $row)
    public function aroundRender(\Magento\Backend\Block\Widget\Grid\Column\Renderer\Price $subject, callable $proceed, \Magento\Framework\DataObject $row)
    {
        //if ($data = $this->_getValue($row)) {
        if ($data = $subject->_getValue($row)) {
//            $currencyCode = $this->_getCurrencyCode($row);
            $currencyCode = $subject->_getCurrencyCode($row);

            if (!$currencyCode) {
                return $data;
            }

//            $data = floatval($data) * $this->_getRate($row);
            $data = floatval($data) * $subject->_getRate($row);
            $data = sprintf("%f", $data);

            //            $data = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data, array('display' => \Zend_Currency::NO_SYMBOL));
            /*
             * \Zend_Currency::NO_SYMBOL -> deprecated since 2.4.6 version of Magento as Magento moving out from Zend Framework to Laminas
             * \Magento\Framework\Currency::NO_SYMBOL -> since 2.4.6 version of Magento
             *
            */
            $data = $subject->_localeCurrency->getCurrency($currencyCode)->toCurrency($data, array('display' => \Magento\Framework\Currency::NO_SYMBOL));
//            return '<input type="text" name="'.$this->getColumn()->getIndex().'" value="'.(($data !=0)? $data : '').'" class="input-text admin__control-text">';
            return '<input type="text" name="'.$subject->getColumn()->getIndex().'" value="'.(($data !=0)? $data : '').'" class="input-text admin__control-text">';
        }

//        return '<input type="text" name="'.$this->getColumn()->getIndex().'" value="'.(($data !=0)? $data : '').'" class="input-text admin__control-text">';
        return '<input type="text" name="'.$subject->getColumn()->getIndex().'" value="'.(($data !=0)? $data : '').'" class="input-text admin__control-text">';
    }
}
