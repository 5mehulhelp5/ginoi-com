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

namespace Mageplaza\GiftCard\Controller\Adminhtml\Template;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\GiftCard\Helper\Template;

/**
 * Class DownLoadImage
 * @package Mageplaza\GiftCard\Controller\Adminhtml\Template
 */
class DownLoadImage extends Action
{

    /**
     * @var Template
     */
    private $template;

    const IS_ADDED_FONT = 'is_added';


    public function __construct(
        Context $context,
        Template $template,
    ) {
        $this->template = $template;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $html      = $this->getRequest()->getParam('content');
        $imageData = $this->template->outputImg(self::IS_ADDED_FONT, $html);
        $imageData = base64_encode($imageData);  // Encode the image data to base64

        return $this->getResponse()->representJson(
            json_encode(['downloadData' => $imageData])
        );
    }
}