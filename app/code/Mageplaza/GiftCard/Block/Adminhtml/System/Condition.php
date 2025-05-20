<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Rule\Block\Conditions as RuleConditions;
use Magento\SalesRule\Model\Rule;
use Mageplaza\GiftCard\Helper\Data as HelperData;

/**
 * Class Condition
 * @package Mageplaza\GiftCard\Block\Adminhtml\System
 */
class Condition extends Field
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;
    /**
     * @var RuleConditions
     */
    protected $_conditions;

    /**
     * @var FormFactory
     */
    protected $_formFactory;

    /**
     * @var Rule
     */
    protected $_rule;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param Fieldset $rendererFieldset
     * @param RuleConditions $conditions
     * @param FormFactory $formFactory
     * @param Rule $rule
     * @param HelperData $helperData
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Fieldset $rendererFieldset,
        RuleConditions $conditions,
        FormFactory $formFactory,
        Rule $rule,
        HelperData $helperData,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions       = $conditions;
        $this->_formFactory      = $formFactory;
        $this->_rule             = $rule;
        $this->_helperData       = $helperData;

        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @param $element
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml($element): string
    {
        $model  = $this->_rule;
        $form   = $this->_formFactory->create();
        $htmlId = $element->getHtmlId();
        $form->setHtmlIdPrefix('rule_' . $htmlId);
        $values                                 = $this->_helperData->getGiveGiftCardConfig('condition',
            $this->_helperData->getStoreId());
        $data['conditions_serialized_multiple'] = $values;
        $data['prefix']                         = $htmlId;
        $model->setData($data);
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNameInLayout('mpgiftcard-layout-rule'
        )->setNewChildUrl(
            $this->getUrl("sales_rule/promo_quote/newConditionHtml/form/{$htmlId}/form_namespace/" . $htmlId)
        )->setFieldSetId($htmlId);

        $fieldset = $form->addFieldset($htmlId . '_fieldset', [
            'legend' => __('Apply the rule only if the following conditions are met (leave blank for all products)'),
        ])->setRenderer($renderer);

        $fieldset->addField($htmlId . '_condition', 'text', [
            'name'  => 'mpgiftcard_product_checkout_condition',
            'label' => __('Condition'),
            'title' => __('Condition'),
        ])->setRule($model)->setRenderer($this->_conditions);

        return $form->toHtml() . $this->getScriptHtml($htmlId);
    }

    /**
     * @param $htmlId
     *
     * @return string
     */
    public function getScriptHtml($htmlId): string
    {
        $inputName = str_replace('mpgiftcard_', '', $htmlId);
        $inputName = str_replace('_condition', '', $inputName);

        return <<<SCRIPT
            <script type="text/javascript">
                require(['jquery'], function ($) {
                    "use strict";
                    $(document).ready(function () {
                        var inputHtml = '<input class="field-none" id="mpgiftcard_product_checkout_condition" name="groups[product][groups][checkout][fields][condition][value]" style="display: none" >';
                        $('#row_mpgiftcard_' + '{$inputName}' + '_condition .value').after(inputHtml);
                    });
                });
            </script>
        SCRIPT;
    }
}
