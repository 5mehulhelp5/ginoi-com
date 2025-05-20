<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;


class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var $templateIdentifier
     */
    protected $templateIdentifier;
    /**
     * @var $templateModel
     */
    protected $templateModel;
    /**
     * @var $templateVars
     */
    protected $templateVars;
    /**
     * @var $templateOptions
     */
    protected $templateOptions;
    /**
     * @var $transport
     */
    protected $transport;
    /**
     * @var $templateFactory
     */
    protected $templateFactory;
    /**
     * @var $objectManager
     */
    protected $objectManager;
    /**
     * @var $message
     */
    protected $message;
    /**
     * @var $_senderResolver
     */
    protected $_senderResolver;
    /**
     * @var $mailTransportFactory
     */
    protected $mailTransportFactory;
    /**
     * @var $messageData
     */
    private $messageData = [];
    /**
     * @var $emailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;
    /**
     * @var $mimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;
    /**
     * @var $mimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;
    /**
     * @var $addressConverter
     */
    private $addressConverter;
    /**
     * @var $attachments
     */
    protected $attachments = [];

    /**
     * Constructor
     *
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param MessageInterfaceFactory|null $messageFactory
     * @param EmailMessageInterfaceFactory|null $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory|null $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory|null $mimePartInterfaceFactory
     * @param AddressConverter|null $addressConverter
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        ?MessageInterfaceFactory $messageFactory = null,
        ?EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        ?MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
        ?MimePartInterfaceFactory $mimePartInterfaceFactory = null,
        ?AddressConverter $addressConverter = null
    ) {
        $this->templateFactory = $templateFactory;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $this->objectManager
            ->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $this->objectManager
            ->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $this->objectManager
            ->get(MimePartInterfaceFactory::class);
        $this->addressConverter = $addressConverter ?: $this->objectManager
            ->get(AddressConverter::class);
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $addressConverter
        );
    }
    /**
     * Send Mail to peoples in CC
     *
     * @param mixed $address
     * @param string $name
     * @return void
     */
    public function addCc($address, $name = '')
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    /**
     * Send Mail to peoples in To
     *
     * @param mixed $address
     * @param string $name
     * @return void
     */
    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * Send Mail to peoples in Bcc
     *
     * @param mixed $address
     * @return void
     */
    public function addBcc($address)
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }
    /**
     * SetReplyTo function
     *
     * @param string $email
     * @param string $name
     * @return void
     */
    public function setReplyTo($email, $name = null)
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }
    /**
     * SetFrom Email
     *
     * @param string $from
     * @return void
     */
    public function setFrom($from)
    {
        return $this->setFromByScope($from);
    }
    /**
     * SetFromByScope function
     *
     * @param string $from
     * @param int $scopeId
     * @return void
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
    }
    /**
     * SetTemplateIdentifier function
     *
     * @param string $templateIdentifier
     * @return void
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;

        return $this;
    }
    /**
     * Set Template Model Function
     *
     * @param mixed $templateModel
     * @return void
     */
    public function setTemplateModel($templateModel)
    {
        $this->templateModel = $templateModel;
        return $this;
    }
    /**
     * Set Template Vars function
     *
     * @param mixed $templateVars
     * @return void
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;

        return $this;
    }
    /**
     * Set template options in mail
     *
     * @param mixed $templateOptions
     * @return void
     */
    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;

        return $this;
    }
    /**
     * Transport Message
     *
     * @return void
     */
    public function getTransport()
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }

        return $mailTransport;
    }
    /**
     * Reset all message data ,identifiers and variables of message
     *
     * @return void
     */
    protected function reset()
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }
    /**
     * Get Template for email
     *
     * @return void
     */
    protected function getTemplate()
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }
    /**
     * PrepareMessage For Mail
     *
     * @return void
     */
    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();
        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $part['type'] = MimeInterface::TYPE_TEXT;
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $part['type'] = MimeInterface::TYPE_HTML;
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }
        $mimePart = $this->mimePartInterfaceFactory->create(['content' => $content]);
        $parts = count($this->attachments) ? array_merge([$mimePart], $this->attachments) : [$mimePart];
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => $parts]
        );
        $messageD = htmlentities(
            (string) $template->getSubject(),
            ENT_QUOTES
        );
        $this->messageData['subject'] = htmlspecialchars_decode($messageD);

        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }
    /**
     * Add Adsress by mail type
     *
     * @param mixed $addressType
     * @param mixed $email
     * @param string $name
     * @return void
     */
    private function addAddressByType($addressType, $email, $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        }
    }

    /**
     * Add Attachments in mail
     *
     * @param mixed $content
     * @param string $file_name
     * @param mixed $fileType
     * @return void
     */
    public function addAttachment($content, $file_name, $fileType)
    {
        $attachmentPart = $this->mimePartInterfaceFactory->create();
        $attachmentPart->setContent($content)
            ->setType($fileType)
            ->setFileName($file_name)
            ->setDisposition(MimeInterface::DISPOSITION_ATTACHMENT)
            ->setEncoding(MimeInterface::ENCODING_BASE64);
        $this->attachments[] = $attachmentPart;

        return $this;
    }
}
