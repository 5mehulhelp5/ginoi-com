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
 * @category  Mageplaza
 * @package   Mageplaza_GiftCard
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper as CoreProductDataMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\GiftCard\Helper\Data as HelperData;

/**
 * Class ProductDataMapper
 * @package Mageplaza\GiftCard\Plugin
 */
class ProductDataMapper
{
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productModel;
    /**
     * @var CustomerGroup
     */
    protected $customerGroup;
    /**
     * @var array
     */
    private $customerGroupArray;

    /**
     * ProductDataMapper constructor.
     *
     * @param HelperData $helperData
     * @param ProductRepositoryInterface $productModel
     * @param CustomerGroup $customerGroup
     */
    public function __construct(
        HelperData $helperData,
        ProductRepositoryInterface $productModel,
        CustomerGroup $customerGroup
    ) {
        $this->helperData    = $helperData;
        $this->productModel  = $productModel;
        $this->customerGroup = $customerGroup;
    }

    /**
     * @param CoreProductDataMapper $subject
     * @param $result
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterMap(CoreProductDataMapper $subject, $result)
    {
        foreach ($result as $productId => $value) {
            $this->setProductData($productId, $value, $result);
        }
        return $result;
    }

    /**
     * @param $productId
     * @param array $productData
     * @param array $result
     * @return void
     * @throws NoSuchEntityException
     */
    public function setProductData($productId, array $productData, array &$result)
    {
        $product = $this->productModel->getById($productId);
        if($product->getData('mpgiftcard_customergroup') !== null){
            foreach ($this->getCustomerGroups() as $customerGroup) {
                $mpGiftCardCustomerGroupCP    = $product->getData('mpgiftcard_customergroup');
                $customerGroupId    = $customerGroup['value'];
                $field              = 'mpgiftcard_customergroup_' . $customerGroupId;
                $mpGiftCardCustomerGroupCP = explode(',', $mpGiftCardCustomerGroupCP);
                if (is_array($mpGiftCardCustomerGroupCP)) {
                    if(in_array($customerGroupId,$mpGiftCardCustomerGroupCP)){
                        $result[$productId][$field] = 1;
                    }else{
                        $result[$productId][$field] = 0;
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        if (!$this->customerGroupArray) {
            $this->customerGroupArray = $this->customerGroup->toOptionArray();
        }

        return $this->customerGroupArray;
    }
}
