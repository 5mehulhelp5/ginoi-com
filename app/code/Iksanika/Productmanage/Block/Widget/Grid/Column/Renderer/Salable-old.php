<?php
/**
 *
 * Copyright © 2015 Iksanika. All rights reserved.
 * See IKS-LICENSE.txt for license details.
 */

namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
//use Magento\InventorySalesAdminUi\Model;

/**
 * Backend grid item renderer number
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Salable extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var GetAssignedStockIdsBySku
     */
    private $getAssignedStockIdsBySku;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockRepositoryInterface $stockRepository
     * @param GetAssignedStockIdsBySku $getAssignedStockIdsBySku
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        StockRepositoryInterface $stockRepository,
        GetAssignedStockIdsBySku $getAssignedStockIdsBySku,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->stockRepository = $stockRepository;
        $this->getAssignedStockIdsBySku = $getAssignedStockIdsBySku;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }






    /**
     * @param string $sku
     * @return array
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SkuIsNotAssignedToStockException
     */
    public function execute(string $sku): array
    {
        $stockInfo = [];
        $sku = htmlspecialchars_decode($sku, ENT_NOQUOTES);
        $stockIds = $this->getAssignedStockIdsBySku->execute($sku);
        if (count($stockIds)) {
            foreach ($stockIds as $stockId) {
                $stockId = (int)$stockId;
                $stock = $this->stockRepository->get($stockId);
                $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
                $isManageStock = $stockItemConfiguration->isManageStock();
                $stockInfo[] = [
                    'stock_id' => $stockId,
                    'stock_name' => $stock->getName(),
                    'qty' => $isManageStock ? $this->getProductSalableQty->execute($sku, $stockId) : null,
                    'manage_stock' => $isManageStock,
                ];
            }
        }
        return $stockInfo;
    }



    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $stockSalable = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        if(
            $row->getTypeId() == 'simple' ||
            $row->getTypeId() == 'virtual'
        )
        {
            $qtys = $stockSalable->execute($row->getSku());

            $out = '';
            foreach($qtys as $qty)
            {
                $out .= '<b>'.$qty['stock_name'].'</b>: '.($qty['qty'] ? $qty['qty'] : '0');
            }
            return $out;
        }else{
            return '';
        }
    }

}



