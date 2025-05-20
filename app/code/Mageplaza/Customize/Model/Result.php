<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.2.60
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mageplaza\Customize\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

class Result extends \Magento\InventoryIndexer\Model\ResourceModel\UpdateLegacyStockStatus
{    /**
 * @var ResourceConnection
 */
    private $resource;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ResourceConnection $resource
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        ResourceConnection $resource,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->resource = $resource;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * Update legacy stock status for given skus.
     *
     * @param array $dataForUpdate
     */
    public function execute(array $dataForUpdate): void
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('cataloginventory_stock_status');
        $productIds = $this->getProductIdsBySkus->execute(array_keys($dataForUpdate));
        foreach ($dataForUpdate as $sku => $isSalable) {
            $connection->update(
                $tableName,
                ['stock_status' => $isSalable],
                ['product_id = ?' => (int) $productIds[$sku]]
            );
        }
    }
}

