<?php
/**
 *
 * Copyright © 2024 Iksanika. All rights reserved.
 * See IKS-LICENSE.txt for license details.
 */

namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
//use Magento\CatalogInventory\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Salable extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var SourceItemRepositoryInterface
     */
    public $sourceItems;

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;


    /**
     * @param SourceItemRepositoryInterface $sourceItems
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(

        SourceItemRepositoryInterface $sourceItems,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ){
        $this->sourceItems = $sourceItems;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        //Set source
        $sku = $row->getSku();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $sku)
            ->create();
        $sourceItemData = $this->sourceItems->getList($searchCriteria);
        $out = '';
        foreach ($sourceItemData->getItems() as $sourceItem)
        {
            $out .= '<b>'.$qty['stock_name'].'</b>: '.($qty['qty'] ? $qty['qty'] : '0');

            //Get source qty
            $out .= ($out != '' ? '<br>' : '').$sourceItem->getSourceCode().': '.$sourceItem->getQuantity();
        }
        return $out;
    }

}



