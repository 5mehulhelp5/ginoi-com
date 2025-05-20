<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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

namespace Mageplaza\GiftCard\Model\Api;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\GiftCard\Api\Data\RedeemDetailInterfaceFactory;
use Mageplaza\GiftCard\Api\GiftCardManagementInterface;
use Mageplaza\GiftCard\Helper\Checkout as CheckoutHelper;
use Mageplaza\GiftCard\Helper\Data;
use Mageplaza\GiftCard\Model\GiftCard;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\Product\DeliveryMethods;
use Mageplaza\GiftCard\Model\TransactionFactory;
use Spipu\Html2Pdf\Exception\Html2PdfException;

/**
 * Class GiftCardManagement
 * @package Mageplaza\GiftCard\Model\Api
 */
class GiftCardManagement implements GiftCardManagementInterface
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var GiftCardFactory
     */
    protected $_giftCardFactory;

    /**
     * @var CheckoutHelper
     */
    protected $_checkoutHelper;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var RedeemDetailInterfaceFactory
     */
    private $redeemDetailFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * GiftCardManagement constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param GiftCardFactory $giftCardFactory
     * @param CheckoutHelper $checkoutHelper
     * @param TransactionFactory $transactionFactory
     * @param RedeemDetailInterfaceFactory $redeemDetailFactory
     * @param Data $helperData
     * @param File $file
     * @param Escaper $escaper
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        GiftCardFactory $giftCardFactory,
        CheckoutHelper $checkoutHelper,
        TransactionFactory $transactionFactory,
        RedeemDetailInterfaceFactory $redeemDetailFactory,
        Data $helperData,
        File $file,
        Escaper $escaper
    ) {
        $this->quoteRepository     = $quoteRepository;
        $this->_checkoutHelper     = $checkoutHelper;
        $this->_giftCardFactory    = $giftCardFactory;
        $this->transactionFactory  = $transactionFactory;
        $this->redeemDetailFactory = $redeemDetailFactory;
        $this->helperData          = $helperData;
        $this->file                = $file;
        $this->escaper             = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $code)
    {
        $code = $this->escaper->escapeHtml($code);
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        try {
            $this->_checkoutHelper->addGiftCards($code, $quote);
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not apply gift card %1', $code));
        }

        $giftCardUsed = $this->_checkoutHelper->getGiftCardsUsed($quote);
        if (!array_key_exists($code, $giftCardUsed)) {
            throw new NoSuchEntityException(__('Gift Card is not valid'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId, $code)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $giftCard = $this->_giftCardFactory->create();
        $giftCard->load($code);
        if (!$giftCard->getId()) {
            $giftCard->loadByCode($code);
        }
        if (!$giftCard->getId()) {
            throw new CouldNotDeleteException(__('Could not cancel gift card'));
        }

        $code = $giftCard->getCode();

        $giftCardUsed = $this->_checkoutHelper->getGiftCardsUsed($quote);
        if (!array_key_exists($code, $giftCardUsed)) {
            throw new NoSuchEntityException(__('Could not cancel gift card'));
        }

        try {
            $this->_checkoutHelper->removeGiftCard($code, false, $quote);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Could not cancel gift card'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function credit($cartId, $amount)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        try {
            $this->_checkoutHelper->applyCredit($amount, $quote);
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not apply gift credit'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function redeem($customerId, $code)
    {
        $giftCard = $this->_giftCardFactory->create()->load($code, 'code');

        if (!$giftCard->canRedeem()) {
            throw new LocalizedException(__('Gift Card "%1" cannot be redeemed.', $code));
        }

        $customer = $this->_checkoutHelper->getCustomer($customerId);

        $this->transactionFactory->create()->redeemGiftCard($customer, $giftCard);

        return $this->redeemDetailFactory->create()
            ->setCustomerBalance($this->_checkoutHelper->getCustomerBalance($customer));
    }

    /**
     * {@inheritdoc}
     */
    public function previewEmail($productData)
    {
        $giftCard = $this->_giftCardFactory->create()->addData($productData);
        $giftCard->setCode($productData['giftcode_pattern']);
        if (isset($productData['expire_after']) && $productData['expire_after']) {
            $timezone  = new DateTimeZone($productData['timezone']);
            $expiredAt = (
            new DateTime('+' . $productData['expire_after'] . ' day', $timezone)
            )->format('Y-m-d');
            $giftCard->setExpiredAt($expiredAt);
        }

        $deliveryMethod = (int) $giftCard->getDeliveryMethod();

        $result = '';
        $params = [];
        switch ($deliveryMethod) {
            case DeliveryMethods::METHOD_PRINT:
                $params['is_print'] = true;
            // no break
            case DeliveryMethods::METHOD_EMAIL:
                $templateFields = $giftCard->getTemplateFields()
                    ? Data::jsonDecode($giftCard->getTemplateFields())
                    : [];

                if (isset($params['is_print'])) {
                    $params['print_true'] = '1';
                } else {
                    $params['print_false'] = '1';
                }

                $params       = array_merge([
                    'sender'          => isset($templateFields['sender']) ? $templateFields['sender'] : '',
                    'recipient'       => isset($templateFields['recipient']) ? $templateFields['recipient'] : '',
                    'message'         => isset($templateFields['message']) ? $templateFields['message'] : '',
                    'balanceFormated' => $this->helperData->convertPrice(
                        $giftCard->getBalance(),
                        true,
                        false
                    ),
                    'status_label'    => $giftCard->getStatusLabel(),
                    'expired_date'    => $giftCard->getExpiredAt()
                        ? $this->helperData->formatDate($giftCard->getExpiredAt())
                        : null,
                    'giftcard'        => $giftCard
                ], $params);
                $fileUrl      = $this->getPreviewGiftCardPdfUrl($giftCard);
                $emailContent = $this->helperData->getEmailHelper()->getEmailTemplate(
                    $this->helperData->getEmailConfig('template'),
                    $params
                );
                $emailHtml    = htmlspecialchars($emailContent->processTemplate());
                $result       = $this->getPreviewEmailHtml($emailHtml, $fileUrl);
                break;
            case DeliveryMethods::METHOD_SMS:
                $smsMessage = $this->helperData->getSmsHelper()->generateMessageContent($giftCard, '');
                $result     = '<div>'
                    . '<textarea readonly>' . $smsMessage . '</textarea>'
                    . '</div>';
                break;
            case DeliveryMethods::METHOD_POST:
                $fileUrl = $this->getPreviewGiftCardPdfUrl($giftCard);
                $result  = '<iframe src="' . $fileUrl . '" style="width: 100%; height: 700px;"></iframe>';
                break;
        }

        return $result;
    }

    /**
     * @param string $emailHtml
     * @param string $contentPreview
     *
     * @return string
     */
    public function getPreviewEmailHtml($emailHtml, $contentPreview)
    {
        $hideAttachment = <<<SCRIPT
        <script type="text/javascript">
            require([
                'jquery'
            ], function ($) {
                if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                    $('.mp-giftcard-pdf-preview').hide();
                    $('.mp-giftcard-pdf-download').show();
                }else{
                    $('.mp-giftcard-pdf-preview').show();
                    $('.mp-giftcard-pdf-download').hide();
                }
            });
        </script>
        SCRIPT;

        if (filter_var($contentPreview, FILTER_VALIDATE_URL) === false) {
            return '<div class="mp-giftcard-preview-email">'
                . '<div class="mp-email-html">'
                . '<iframe srcdoc="' . $emailHtml . '" style="width: 100%; height: 350px" allowfullscreen></iframe>'
                . '</div>'
                . '<div class="mp-giftcard-html">'
                . '<label>' . __('Attachment') . '</label>'
                . '<div class="control" style="padding-bottom: 50px;">'
                . '<div style="width: 100%; height: 500px; zoom: 1.5">' . $contentPreview . ' </div>'
                . '<a class="mp-giftcard-pdf-download" download style="height:100px;" href="#">' . __('Download Image file') . '</a>'
                . '</div>'
                . '</div>'
                . '</div>' . $hideAttachment;
        }

        return '<div class="mp-giftcard-preview-email">'
            . '<div class="mp-email-html">'
            . '<iframe srcdoc="' . $emailHtml . '" style="width: 100%; height: 350px" allowfullscreen></iframe>'
            . '</div>'
            . '<div class="mp-giftcard-html">'
            . '<label>' . __('Attachment') . '</label>'
            . '<div class="control" style="padding-bottom: 50px;">'
            . '<iframe class="mp-giftcard-pdf-preview" src="' . $contentPreview . '" style="width: 100%; height: 500px"></iframe>'
            . '<a class="mp-giftcard-pdf-download" download style="height:100px;" href="' . $contentPreview . '">' . __('Download preview PDF file') . '</a>'
            . '</div>'
            . '</div>'
            . '</div>' . $hideAttachment;
    }

    /**
     * @param GiftCard $giftCard
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws Html2PdfException
     * @throws Exception
     */
    public function getPreviewGiftCardPdfUrl($giftCard)
    {
        if (!$giftCard->getTemplateId()) {
            return false;
        }

        $templateHelper = $this->helperData->getTemplateHelper();
        $mediaDirectory = $templateHelper->getMediaDirectory();
        $reviewContent  = $templateHelper->outputGiftCardPdf($giftCard, 's', null, null, true);
        if (substr($reviewContent, 0, 4) !== '%PDF') {
            return $reviewContent;
        }

        $timeStamp = time();
        $filePath  = $mediaDirectory->getAbsolutePath(
            $templateHelper->getTmpMediaPath($timeStamp . 'preview-gift-card.pdf')
        );
        $this->file->checkAndCreateFolder($mediaDirectory->getAbsolutePath($templateHelper->getBaseTmpMediaPath()));
        $this->file->write($filePath, $reviewContent);
        $fileUrl = $this->helperData->getTemplateHelper()->getTmpMediaUrl($timeStamp . 'preview-gift-card.pdf');

        return $fileUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function addList($customerId, $code)
    {
        $giftCard = $this->_giftCardFactory->create();
        $giftCard->load($code, 'code');

        if (!$giftCard->getId()) {
            throw new LocalizedException(__('Invalid gift card code.'));
        }

        $customerIds = $giftCard->getCustomerIds() ? explode(',', $giftCard->getCustomerIds()) : [];
        if ($giftCard->isActive()) {
            if (!in_array($customerId, $customerIds, true)) {
                $customerIds[] = $customerId;
            }
            $giftCard->setCustomerIds(implode(',', $customerIds))->save();

            return $giftCard->getGiftCardListForCustomer($customerId);
        }

        throw new LocalizedException(__('Invalid gift card code.'));
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromList($customerId, $code)
    {
        $giftCard = $this->_giftCardFactory->create();
        $giftCard->load($code, 'code');

        if (!$giftCard->getId()) {
            throw new LocalizedException(__('Invalid gift card code.'));
        }

        $customerIds = $giftCard->getCustomerIds() ? explode(',', $giftCard->getCustomerIds()) : [];
        if (($key = array_search($customerId, $customerIds, true)) !== false) {
            unset($customerIds[$key]);
            $giftCard->setCustomerIds(implode(',', $customerIds))->save();

            return $giftCard->getGiftCardListForCustomer($customerId);
        }

        throw new LocalizedException(__('Invalid gift card code.'));
    }
}
