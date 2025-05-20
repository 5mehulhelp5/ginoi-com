<?php

namespace Meetanshi\CustomCarrierTracking\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Status
 * @package Meetanshi\CustomCarrierTracking\Ui\Component\Listing\Column
 */
class Status extends Column
{
    /**
     * Status constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {

                $state = $item[$fieldName];
                if ($state == 1) {
                    $colour = "10a900";
                    $value = "Active";
                } else {
                    $colour = "ff031b";
                    $value = "Inactive";
                }
                $item[$fieldName] = '<div style="text-align:center;width: 110px !important;    padding: 5px 0; color:#FFF;background-color:#' . $colour . ';border-radius:8px;width:100%">' . $value . '</div>';
            }
        }
        return $dataSource;
    }
}
