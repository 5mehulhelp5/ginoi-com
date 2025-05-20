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

namespace Mageplaza\GiftCard\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\GiftCard\Helper\SynChronized as Data;
use PHPUnit\Exception;

/**
 * Class Generate
 * @package Mageplaza\GiftCard\Controller\Adminhtml\Report
 */
class Generate extends Action
{
    const REDIRECT_PATCH = 'adminhtml/system_config/edit/section/mpgiftcard';

    /**
     * @var Data
     */
    protected $moduleHelper;


    /**
     * Generate constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
    ) {
        $this->moduleHelper = $dataHelper;

        parent::__construct($context);
    }

    /**
     * execute js file data for all store & customer group
     * then redirect back to the system page
     */
    public function execute()
    {
        try {
            $this->moduleHelper->synchronizedCodeProduct();
            $productGiftCardId = $this->moduleHelper->synchronizedGiftCardProduct();
            $this->messageManager->addSuccessMessage(
                __('%1 Gift products have been synchronized and updated successfully.', count($productGiftCardId))
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Fail To Synchronize And Update Data For The Gift Card Report Section.'));
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect(self::REDIRECT_PATCH);
    }


}

