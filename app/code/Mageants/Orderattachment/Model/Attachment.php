<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Model;

use Mageants\Orderattachment\Api\Data\AttachmentInterface as AttachmentInt;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Attachment extends AbstractModel implements AttachmentInt, IdentityInterface
{
    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    public const XML_PATH_ATTACHMENT_ON_ATTACHMENT_INFORMATION = 'orderattachments/general/attachment_information';

    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    public const XML_PATH_ATTACHMENT_ON_DISPLAY_ATTACHMENT = 'orderattachments/general/display_attachment';

    /**
     * XML configuration paths for "File restrictions - limit" property
     */
    public const XML_PATH_ATTACHMENT_FILE_LIMIT = 'orderattachments/general/count';
    /**
     * XML configuration paths for "File restrictions - size" property
     */
    public const XML_PATH_ATTACHMENT_FILE_SIZE = 'orderattachments/general/size';

    /**
     * XML configuration paths for "File restrictions - Allowed extensions" property
     */
    public const XML_PATH_ATTACHMENT_FILE_EXT = 'orderattachments/general/extension';
    /**
     * XML configuration paths for "Enabled orderattachment module" property
     */
    public const XML_PATH_ENABLE_ATTACHMENT = 'orderattachments/general/enabled';
    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    public const XML_PATH_ATTACHMENT_ON_ATTACHMENT_TITLE = 'orderattachments/general/attachment_title';

    public const XML_PATH_EMAIL_TEMPLATE = 'orderattachments/demo/request_email_tepmlate';
    
    public const XML_PATH_FRONTEND_ENABLED = 'orderattachments/frontend_demo/Fenabled';
    
    public const XML_PATH_ADMIN_EMAIL = 'orderattachments/frontend_demo/admin_email';

    public const XML_PATH_ADMIN_GENERALEMAIL = 'orderattachments/demo/adminmail';
    /**
     * XML configuration paths for "Allow file upload during checkout" property
     */
    public const DEFAULT_TITLE_ATTACHMENT = 'Order Attachment';
    /**
     * cache tag
     */
    public const CACHE_TAG = 'orderattachment_attachment';

    /**
     * @var string
     */
    protected $_cacheTag = 'orderattachment_attachment';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'orderattachment_attachment';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Attachment::class);
    }
    /**
     * Get OrderAttachments function
     *
     * @param mixed $orderId
     * @return void
     */
    public function getOrderAttachments($orderId)
    {
        return $this->_getResource()->getOrderAttachments($orderId);
    }

    /**
     * Get AttachmentsByQuote function
     *
     * @param mixed $quoteId
     * @return void
     */
    public function getAttachmentsByQuote($quoteId)
    {
        return $this->_getResource()->getAttachmentsByQuote($quoteId);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get AttachmentId function
     *
     * @return void
     */
    public function getAttachmentId()
    {
        return $this->getData(self::ATTACHMENT_ID);
    }

    /**
     * Get QuoteId function
     *
     * @return void
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

   /**
    * Get OrderId function
    *
    * @return void
    */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

   /**
    * Get Path function
    *
    * @return void
    */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * GetComment function
     *
     * @return void
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * GetHash function
     *
     * @return void
     */
    public function getHash()
    {
        return $this->getData(self::HASH);
    }

    /**
     * Get Type function
     *
     * @return void
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get UploadedAt function
     *
     * @return void
     */
    public function getUploadedAt()
    {
        return $this->getData(self::UPLOADED_AT);
    }

   /**
    * GetModifiedAt function
    *
    * @return void
    */
    public function getModifiedAt()
    {
        return $this->getData(self::MODIFIED_AT);
    }
    /**
     * Set AttachmentId function
     *
     * @param mixed $AttachmentId
     * @return void
     */
    public function setAttachmentId($AttachmentId)
    {
        return $this->setData(self::ATTACHMENT_ID, $AttachmentId);
    }
    /**
     * SetQuoteId function
     *
     * @param mixed $QuoteId
     * @return void
     */
    public function setQuoteId($QuoteId)
    {
        return $this->setData(self::QUOTE_ID, $QuoteId);
    }
    /**
     * SetOrderId function
     *
     * @param mixed $OrderId
     * @return void
     */
    public function setOrderId($OrderId)
    {
        return $this->setData(self::ORDER_ID, $OrderId);
    }
    /**
     * SetPath function
     *
     * @param mixed $Path
     * @return void
     */
    public function setPath($Path)
    {
        return $this->setData(self::PATH, $Path);
    }
    /**
     * SetComment function
     *
     * @param mixed $Comment
     * @return void
     */
    public function setComment($Comment)
    {
        return $this->setData(self::COMMENT, $Comment);
    }
    /**
     * SetHash function
     *
     * @param mixed $Hash
     * @return void
     */
    public function setHash($Hash)
    {
        return $this->setData(self::HASH, $Hash);
    }
    /**
     * SetType function
     *
     * @param mixed $Type
     * @return void
     */
    public function setType($Type)
    {
        return $this->setData(self::TYPE, $Type);
    }
    /**
     * SetUploadedAt function
     *
     * @param mixed $UploadedAt
     * @return void
     */
    public function setUploadedAt($UploadedAt)
    {
        return $this->setData(self::UPLOADED_AT, $UploadedAt);
    }

    /**
     * SetModifiedAt function
     *
     * @param mixed $ModifiedAt
     * @return void
     */
    public function setModifiedAt($ModifiedAt)
    {
        return $this->setData(self::MODIFIED_AT, $ModifiedAt);
    }
}
