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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\SalesRule\Model\Rule;

/**
 * Class Condition
 * @package Mageplaza\GiftCard\Model\Config\Backend
 */
class Condition extends Value
{
    /**
     * @var Rule
     */
    protected $_rule;

    /**
     * @var Json
     */
    protected $_jsonSerializer;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Condition constructor.
     *
     * @param Rule $rule
     * @param Json $jsonSerializer
     * @param RequestInterface $request
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Rule $rule,
        Json $jsonSerializer,
        RequestInterface $request,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_rule           = $rule;
        $this->_jsonSerializer = $jsonSerializer;
        $this->_request        = $request;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }


    /**
     * @return void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $params    = $this->_request->getParams();
        $fieldName = str_replace('/', '_', $this->getPath());
        if (!empty($this->getValue() && $fieldName === 'mpgiftcard_email_send')) {
            $valueArray = explode(',', $this->getValue());
            foreach ($valueArray as $value) {
                $value = trim($value);
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidatorException(__('%1 is not a valid email address.', $value));
                }
            }
        }else {
            $this->setValue($this->getConditions($params['rule'][$fieldName] ?? []));
        }
        parent::beforeSave();
    }

    /**
     * @param $conditions
     * @return bool|string
     */
    protected function getConditions($conditions)
    {
        if ($conditions) {
            $rule               = $this->_rule;
            $data['conditions'] = $conditions;
            $rule->loadPost($data);

            return $this->serializeCondition($rule->getConditions()->asArray());
        }

        return '';
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function serializeCondition($data)
    {
        return $this->_jsonSerializer->serialize($data);
    }
}
