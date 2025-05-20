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
 * @category   Mageplaza
 * @package    Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Plugin\SystemConfig;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;

/**
 * Class ConditionRule
 * @package Mageplaza\GiftCard\Plugin\SystemConfig
 */
class ConditionRule
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Json
     */
    protected $_jsonSerializer;

    /**
     * @var array|false[]
     */
    protected $condition = [];

    /**
     * ConditionRule constructor.
     *
     * @param RequestInterface $request
     * @param Json $jsonSerializer
     */
    public function __construct(
        RequestInterface $request,
        Json $jsonSerializer
    ) {
        $this->request         = $request;
        $this->_jsonSerializer = $jsonSerializer;
        $this->condition       = [
            'mpgiftcard_product_checkout_condition' => false
        ];
    }

    /**
     * @param $subject
     * @return void
     */
    public function beforeGetTypeElement($subject): void
    {
        if ($this->request->getParam('section') === 'mpgiftcard' && $subject->getRule()) {
            $prefix = $subject->getRule()->getPrefix() ?: 'condition';
            $subject->setData('prefix', $prefix);
            if ($subject->getRule()->getData('conditions_serialized_multiple')) {
                if ($subject->getData($prefix) === null) {
                    if ($this->condition[$prefix]) {
                        $subject->setData($prefix, []);
                    } else {
                        /** @var Combine $combine */
                        $combine = clone $subject;
                        $combine = $combine->loadArray($this->getConditions($combine));
                        $subject->setData($prefix, $combine->getData($prefix) ?: []);
                        $this->condition[$prefix] = true;
                    }
                }
            } else {
                $subject->setData($prefix, []);
            }
        }
        if ($this->request->getParam('form') && str_contains($this->request->getParam('form'), 'mpgiftcard')) {
            $prefix = $this->request->getParam('form_namespace') ?: 'conditions';
            $subject->setData('prefix', $prefix);
            $subject->setData($prefix, []);
        }
    }


    /**
     * @param $combine
     * @return mixed
     */
    public function getConditions($combine): mixed
    {
        $conditionsSerialized = $combine->getRule()->getData('conditions_serialized_multiple');

        return $this->_jsonSerializer->unserialize($conditionsSerialized);
    }
}
