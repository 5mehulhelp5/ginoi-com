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

namespace Mageplaza\GiftCard\Model\ResourceModel\Report\Grid;

use Magento\Backend\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Item;
use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Mageplaza\GiftCard\Helper\Data as HelperData;

/**
 * Class Collection
 * @package Mageplaza\GiftCard\Model\ResourceModel\Report\Grid
 */
class Collection extends SearchResult
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Session
     */
    protected $_backendSession;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param HelperData $helperData
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param CollectionFactory $orderItemCollectionFactory
     * @param Session $backendSession
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory     $entityFactory,
        Logger            $logger,
        HelperData        $helperData,
        FetchStrategy     $fetchStrategy,
        EventManager      $eventManager,
        RequestInterface  $request,
        CollectionFactory $orderItemCollectionFactory,
        Session           $backendSession,
        string            $mainTable = 'mageplaza_giftcard',
        string            $resourceModel = Item::class
    ) {
        $this->_request = $request;
        $this->_backendSession = $backendSession;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_helperData = $helperData;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $fields = ['created_at'];
        foreach ($fields as $field) {
            $this->addFilterToMap($field, 'main_table.' . $field);
        }

        if ($this->_request->getParam('current_product_id')) {
            $this->_backendSession->setData('current_product_id', $this->_request->getParam('current_product_id'));
        }

        if ($this->_request->getParam('namespace') === 'giftcard_report_listing') {
            $fullActionName = $this->_request->getFullActionName();

            if ($fullActionName === 'mui_index_render'
                || $fullActionName === 'mui_export_gridToCsv'
                || $fullActionName === 'mui_export_gridToXml') {

                $this->addFieldToFilter(
                    'main_table.product_id',
                    $this->_request->getParam('current_product_id')
                );
            }
        }

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_filter') {
            $this->getSelect()->where("main_table.store_id = '{$condition['eq']}'");

            return $this;
        }

        if ($field === 'row_total_incl_tax') {
            if (isset($condition['gteq'])) {
                $this->getSelect()->where("main_table.row_total_incl_tax >= '{$condition['gteq']}'");
            }
            if (isset($condition['lteq'])) {
                $this->getSelect()->where("main_table.row_total_incl_tax<= '{$condition['lteq']}'");
            }

            return $this;
        }

        if ($field === 'original_price') {
            if (isset($condition['gteq'])) {
                $this->getSelect()->where("main_table.original_price >= '{$condition['gteq']}'");
            }
            if (isset($condition['lteq'])) {
                $this->getSelect()->where("main_table.original_price<= '{$condition['lteq']}'");
            }

            return $this;
        }

        if ($field === 'created_at') {
            $field = 'main_table.created_at';
        }

        return parent::addFieldToFilter($field, $condition);
    }

}

