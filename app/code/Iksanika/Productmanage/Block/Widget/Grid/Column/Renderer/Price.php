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
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($data = $this->_getValue($row)) {
            $currencyCode = $this->_getCurrencyCode($row);

            if (!$currencyCode) {
                return $data;
            }

            $data = floatval($data) * $this->_getRate($row);
            $data = sprintf("%f", $data);

            /*
             * \Zend_Currency::NO_SYMBOL -> deprecated since 2.4.6 version of Magento as Magento moving out from Zend Framework to Laminas
             * \Magento\Framework\Currency::NO_SYMBOL -> since 2.4.6 version of Magento
             *
            */

            $symbol = NULL;
            if(class_exists("Zend_Currency")) {
                $symbol = \Zend_Currency::NO_SYMBOL;
            }
            if(class_exists("Magento\Framework\Currency")) {
                $symbol = \Magento\Framework\Currency::NO_SYMBOL;
            }

            $data = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data, array('display' => $symbol));
            return '<input type="text" name="'.$this->getColumn()->getIndex().'" value="'.(($data !=0)? $data : '').'" class="input-text admin__control-text">';
        }

        return '<input type="text" name="'.$this->getColumn()->getIndex().'" value="'.(($data !=0)? $data : '').'" class="input-text admin__control-text">';
    }
}
