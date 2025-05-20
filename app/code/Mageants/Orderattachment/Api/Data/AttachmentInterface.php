<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Api\Data;

interface AttachmentInterface
{
    public const ATTACHMENT_ID = 'attachment_id';
    public const QUOTE_ID = 'quote_id';
    public const ORDER_ID = 'order_id';
    public const PATH = 'path';
    public const COMMENT = 'comment';
    public const HASH = 'hash';
    public const TYPE = 'type';
    public const UPLOADED_AT = 'uploaded_at';
    public const MODIFIED_AT = 'modified_at';

    /**
     * GetAttachmentId function
     *
     * @return void
     */
    public function getAttachmentId();

    /**
     * GetQuoteId function
     *
     * @return void
     */
    public function getQuoteId();

    /**
     * GetOrderId function
     *
     * @return void
     */
    public function getOrderId();

    /**
     * GetPath function
     *
     * @return void
     */
    public function getPath();

    /**
     * GetComment function
     *
     * @return void
     */
    public function getComment();

    /**
     * GetHash function
     *
     * @return void
     */
    public function getHash();

    /**
     * GetType function
     *
     * @return void
     */
    public function getType();

    /**
     * GetUploadedAt function
     *
     * @return void
     */
    public function getUploadedAt();

    /**
     * GetModifiedAt function
     *
     * @return void
     */
    public function getModifiedAt();

    /**
     * SetAttachmentId function
     *
     * @param int $AttachmentId
     * @return void
     */
    public function setAttachmentId($AttachmentId);

    /**
     * SetQuoteId function
     *
     * @param int $QuoteId
     * @return void
     */
    public function setQuoteId($QuoteId);

    /**
     * SetOrderId function
     *
     * @param mixed $OrderId
     * @return void
     */
    public function setOrderId($OrderId);

    /**
     * SetPath function
     *
     * @param string $Path
     * @return void
     */
    public function setPath($Path);

    /**
     * SetComment function
     *
     * @param string $Comment
     * @return void
     */
    public function setComment($Comment);

    /**
     * SetHash function
     *
     * @param mixed $Hash
     * @return void
     */
    public function setHash($Hash);

    /**
     * SetType function
     *
     * @param mixed $Type
     * @return void
     */
    public function setType($Type);

    /**
     * SetUploadedAt function
     *
     * @param mixed $UploadedAt
     * @return void
     */
    public function setUploadedAt($UploadedAt);

    /**
     * SetModifiedAt function
     *
     * @param mixed $ModifiedAt
     * @return void
     */
    public function setModifiedAt($ModifiedAt);
}
