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

namespace Mageplaza\GiftCard\Helper;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Validator\EmailAddress;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Mail\Template\TransportBuilder;
use Mageplaza\GiftCard\Model\Credit;
use Mageplaza\GiftCard\Model\GiftCard;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Laminas\Mime\Decode;
use Laminas\Mail\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

/**
 * Class Email
 * @package Mageplaza\GiftCard\Helper
 */
class Email extends Data
{
    const EMAIL_TYPE_DELIVERY      = '';
    const EMAIL_TYPE_UPDATE        = 'update';
    const EMAIL_TYPE_EXPIRE        = 'before_expire';
    const EMAIL_TYPE_NOTICE_SENDER = 'notify_sender';
    const EMAIL_TYPE_UNUSED        = 'after_unused';
    const EMAIL_TYPE_CREDIT        = 'credit';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var array
     */
    protected $emailParam = [];

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var FactoryInterface
     */
    private $templateFactory;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param EmailAddress $emailAddress
     * @param CustomerFactory $customerFactory
     * @param FactoryInterface $templateFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        EmailAddress $emailAddress,
        CustomerFactory $customerFactory,
        FactoryInterface $templateFactory
    ) {
        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->emailAddress      = $emailAddress;
        $this->customerFactory   = $customerFactory;
        $this->templateFactory   = $templateFactory;

        parent::__construct($context, $objectManager, $storeManager, $localeDate, $customerSession);
    }

    /**
     * Send email to recipient
     *
     * @param GiftCard $giftCard
     * @param $type
     * @param array $params
     *
     * @return $this
     * @throws Exception
     * @throws Html2PdfException
     */
    public function sendDeliveryEmail($giftCard, $type, $params = [])
    {
        if (!$this->isEmailEnable($type, $giftCard->getStoreId())
            || !$this->emailAddress->isValid($giftCard->getDeliveryAddress())) {
            return $this;
        }

        $attachment = ($type === self::EMAIL_TYPE_DELIVERY)
            ? $this->getTemplateHelper()->outputGiftCardPdf($giftCard, 's', TransportBuilder::ATTACHMENT_NAME)
            : null;

        $params = $this->prepareEmailParam($giftCard, $params);
        $emails = $this->getEmail();
        array_push($emails, $giftCard->getDeliveryAddress());

        foreach ($emails as $email) {
            $this->sendEmailTemplate(
                $type,
                $params['recipient'],
                $email,
                $params,
                $giftCard->getStoreId(),
                $attachment
            );
        }

        return $this;
    }

    /**
     * Send email to sender
     *
     * @param GiftCard $giftCard
     * @param $type
     * @param array $params
     *
     * @return $this
     * @throws Exception
     */
    public function sendNoticeSenderEmail($giftCard, $type, $params = [])
    {
        if (!$this->isEmailEnable($type, $giftCard->getStoreId())) {
            return $this;
        }

        $order = $this->getGiftCardOrder($giftCard);
        if (!$order || !$order->getId()) {
            return $this;
        }

        $customerEmail = $order->getCustomerEmail();

        /** @var Store $store */
        $store = $this->storeManager->getStore($giftCard->getStoreId());

        /** @var Customer $customer */
        $customer = $this->customerFactory->create();
        $customer->setStore($store)->loadByEmail($customerEmail);
        if ($customer->getId()) {
            $credit       = $this->objectManager->create(Credit::class)->load($customer->getId(), 'customer_id');
            $notification = $credit->getGiftcardNotification() === null
                ? true : (boolean) $credit->getGiftcardNotification();
            if (!$notification) {
                return $this;
            }
        }

        $params = $this->prepareEmailParam($giftCard, $params);
        $emails = $this->getEmail();
        array_push($emails, $giftCard->getDeliveryAddress());

        foreach ($emails as $email) {
            $this->sendEmailTemplate(
                $type,
                $params['sender'],
                $email,
                $params,
                $store->getId()
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getEmail()
    {
        $result = [];
        if ($this->getEmailConfig('send')) {
            $emails = explode(',', $this->getEmailConfig('send'));
            foreach ($emails as $email) {
                $email    = trim($email);
                $result[] = $email;
            }
        }

        return $result;
    }

    /**
     * @param GiftCard $giftCard
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    protected function prepareEmailParam($giftCard, $params)
    {
        $gcId = $giftCard->getId();
        if (!isset($this->emailParam[$gcId])) {
            $templateFields = $giftCard->getTemplateFields() ? self::jsonDecode($giftCard->getTemplateFields()) : [];

            if (isset($params['is_print'])) {
                $params['print_true'] = 1;
            } else {
                $params['print_false'] = 1;
            }

            $this->emailParam[$gcId] = array_merge([
                'sender'          => isset($templateFields['sender']) ? $templateFields['sender'] : '',
                'recipient'       => isset($templateFields['recipient']) ? $templateFields['recipient'] : '',
                'message'         => isset($templateFields['message']) ? $templateFields['message'] : '',
                'balanceFormated' => $this->convertPrice($giftCard->getBalance(), true, false, $giftCard->getStoreId()),
                'status_label'    => $giftCard->getStatusLabel(),
                'expired_date'    => $giftCard->getExpiredAt() ? $this->formatDate($giftCard->getExpiredAt()) : null,
                'hidden_code'     => $giftCard->getHiddenCode(),
                'giftcard'        => $giftCard
            ], $params);
        }

        return $this->emailParam[$gcId];
    }

    /**
     * @param       $type
     * @param       $toName
     * @param       $toEmail
     * @param array $templateParams
     * @param null $storeId
     * @param null $attachFile
     *
     * @return $this
     * @throws Exception
     */
    public function sendEmailTemplate(
        $type,
        $toName,
        $toEmail,
        $templateParams = [],
        $storeId = null,
        $attachFile = null
    ) {
        $this->inlineTranslation->suspend();

        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $template = $this->getEmailConfig($type ? $type . '/template' : 'template', $storeId);
        $sender   = $this->getEmailConfig('sender', $storeId);

        try {
            $transportBuilder = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($sender)
                ->addTo($toEmail, $toName);
            $transport        = $transportBuilder->getTransport();
            if ($attachFile) {
                $attachPDF = $transportBuilder->addAttachment($attachFile);
                if ($this->versionCompare('2.2.8')) {
                    $html    = $transport->getMessage();
                    $message = Message::fromString($html->getRawMessage());
                    $body    = $message->getBody();
                    if ($this->versionCompare('2.3.3')) {
                        $body = quoted_printable_decode($body);
                    }
                    $part = new Part($body);
                    $part->setCharset('utf-8');
                    if ($this->versionCompare('2.3.3')) {
                        $part->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
                        $part->setDisposition(Mime::DISPOSITION_INLINE);
                    }
                    $part->setType(Mime::TYPE_HTML);
                    $bodyPart = new \Laminas\Mime\Message();
                    $bodyPart->setParts([$part, $attachPDF]);
                    $html->setBody($bodyPart);
                }
            }
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        } catch (Exception $e) {
            $this->inlineTranslation->resume();
            throw $e;
        }

        return $this;
    }

    /**
     * @param string $templateIdentifier
     * @param array $templateVars
     * @param null|mixed $storeId
     *
     * @return TemplateInterface
     * @throws NoSuchEntityException
     */
    public function getEmailTemplate($templateIdentifier, $templateVars, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $this->templateFactory->get($templateIdentifier)
            ->setVars($templateVars)
            ->setOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId]);
    }
}
