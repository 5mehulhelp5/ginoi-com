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
use finfo;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Config\Model\Config\Structure\Element\Dependency\Field;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\Uploader as ApiUploader;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Mageplaza\GiftCard\Api\Data\GiftCodeInterface;
use Mageplaza\GiftCard\Controller\Adminhtml\Template\DownLoadImage;
use Mageplaza\GiftCard\Model\GiftCard;
use Mageplaza\GiftCard\Model\GiftCard\Status;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\Pool;
use Mageplaza\GiftCard\Model\Source\Fonts;
use Mageplaza\GiftCard\Model\TemplateFactory;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\Exceptions\BarcodeException;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
use Stringable;
use TCPDF_FONTS;

/**
 * Class Template
 * @package Mageplaza\GiftCard\Helper
 */
class Template extends Data
{
    const TEMPLATE_MEDIA_PATH = 'mageplaza/giftcard';

    /**
     * @var string
     */
    protected $placeHolderImage;

    /**
     * @var ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var Logo
     */
    protected $_logo;

    /**
     * @var File
     */
    private $file;

    /**
     * @var GiftCardFactory
     */
    public $giftCardFactory;

    /**
     * @var Fonts
     */
    private $fonts;

    /**
     * @var ApiUploader
     */
    private $apiUploader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $_backendUrl;

    /**
     * Template constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerSession $customerSession
     * @param Filesystem $filesystem
     * @param Repository $assetRepo
     * @param TemplateFactory $templateFactory
     * @param FieldFactory $fieldFactory
     * @param Escaper $escaper
     * @param Logo $logo
     * @param File $file
     * @param GiftCardFactory $giftCardFactory
     * @param Fonts $fonts
     * @param ApiUploader $apiUploader
     * @param Emulation $emulation
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        Filesystem $filesystem,
        Repository $assetRepo,
        TemplateFactory $templateFactory,
        FieldFactory $fieldFactory,
        Escaper $escaper,
        Logo $logo,
        File $file,
        GiftCardFactory $giftCardFactory,
        Fonts $fonts,
        ApiUploader $apiUploader,
        Emulation $emulation,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->_logo           = $logo;
        $this->_assetRepo      = $assetRepo;
        $this->templateFactory = $templateFactory;
        $this->_fieldFactory   = $fieldFactory;
        $this->_escaper        = $escaper;
        $this->mediaDirectory  = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->file            = $file;
        $this->giftCardFactory = $giftCardFactory;
        $this->fonts           = $fonts;
        $this->apiUploader     = $apiUploader;
        $this->filesystem      = $filesystem;
        $this->emulation       = $emulation;
        $this->_backendUrl     = $backendUrl;

        parent::__construct($context, $objectManager, $storeManager, $localeDate, $customerSession);
    }

    /**
     * @return WriteInterface
     */
    public function getMediaDirectory()
    {
        return $this->mediaDirectory;
    }

    /**
     * @param $templateId
     *
     * @return \Mageplaza\GiftCard\Model\Template | null
     */
    public function getTemplateById($templateId)
    {
        return $this->templateFactory->create()->load($templateId);
    }

    /**
     * @param $template
     * @param bool $mergeCss
     * @param bool $includeSampleContent
     * @param bool $isPrint
     *
     * @return array
     */
    public function prepareTemplateData($template, $mergeCss = false, $includeSampleContent = false, $isPrint = false)
    {
        $design = (isset($template['design']) && $template['design'])
            ? self::jsonDecode($template['design'])
            : [];
        if (!count($design) || !isset($design['giftcard'])) {
            return [];
        }

        $images         = [];
        $templateImages = (isset($template['images']) && $template['images'])
            ? self::jsonDecode($template['images'])
            : [];
        foreach ($templateImages as $image) {
            $file = $this->mediaDirectory->getRelativePath($this->getMediaPath($image['file']));
            if ($this->mediaDirectory->isFile($file)) {
                $images[] = [
                    'file' => $image['file'],
                    'src'  => $isPrint
                        ? $this->mediaDirectory->getAbsolutePath($this->getMediaPath($image['file']))
                        : $this->getMediaUrl($image['file']),
                    'alt'  => $image['label'] ?: __('Gift Card Image')
                ];
            }
        }

        $initFields = $this->getTemplateFields();

        foreach ($design as $id => &$field) {
            $css = [];

            $field['background-color'] = isset($field['backgroundColor']) ? $field['backgroundColor'] : 'transparent';
            $field['border-color']     = isset($field['borderColor']) ? $field['borderColor'] : 'transparent';
            $field['border-width']     = isset($field['borderWidth']) ? $field['borderWidth'] . 'px' : '0';
            $field['border-radius']    = isset($field['borderRadius']) ? $field['borderRadius'] . 'px' : '0';
            if ($id !== 'logo') {
                $field['font-size'] = isset($field['fontSize']) ? $field['fontSize'] . 'px' : '14px';
            }
            $field['text-align'] = isset($field['align']) ? $field['align'] : '';
            $field['color']      = isset($field['textColor']) ? $field['textColor'] : '#000000';
            isset($field['border']) ?? $field['border'] = strtolower($field['border']);
            isset($field['bold']) ?? $field['font-weight'] = $field['bold'];
            isset($field['italic']) ?? $field['font-style'] = $field['italic'];
            isset($field['underline']) ?? $field['text-decoration'] = $field['underline'];

            foreach ($field as $key => $value) {
                if (!in_array($key, [
                    'width',
                    'height',
                    'top',
                    'left',
                    'background-color',
                    'border-color',
                    'border-width',
                    'border-radius',
                    'font-weight',
                    'font-style',
                    'text-decoration',
                    'font-size',
                    'text-align',
                    'border',
                    'color'
                ])) {
                    $css = ($key === 'css') ? $value : [];
                    unset($field[$key]);
                }
            }
            $field = array_merge($css, $field);

            // Add css for giftcard
            if ($id === 'giftcard') {
                if (isset($template['text_color']) && $template['text_color']) {
                    $field['color'] = '#' . trim($template['text_color'], '#');
                }

                $background = '';
                if (isset($template['background_color']) && $template['background_color']) {
                    $background .= '#' . trim($template['background_color'], '#');
                }
                if (isset($template['background_image']) && $template['background_image']) {
                    if (isset($template['background_image_src']) && $src = $template['background_image_src']) {
                        $imageContent = $this->file->read($src);
                        $url          =
                            $this->mediaDirectory->getAbsolutePath($this->getTmpMediaPath('background_image'));
                        $this->file->write($url, $imageContent);
                    } else {
                        $bgImage = str_replace(self::TEMPLATE_MEDIA_PATH, '', $template['background_image']);
                        $url     = $isPrint
                            ? $this->mediaDirectory->getAbsolutePath($this->getMediaPath($bgImage))
                            : $this->getMediaUrl($bgImage);
                    }

                    $background .= ' url(' . $url . ') no-repeat left top';
                }
                if ($background) {
                    $field['background'] = $field['background-color'] . " $background";
                    unset($field['background-color']);
                }
            }

            // Merge css into 1 field
            if ($mergeCss) {
                $css = '';
                if ($isPrint && isset($field['padding'])) {
                    $field['height'] -= (float) $field['padding'] * 2;
                    $field['width']  -= (float) $field['padding'] * 2;
                }

                foreach ($field as $key => $value) {
                    $css .= $key . ': ' . $value;
                    if (in_array($key, ['width', 'height', 'top', 'left'])) {
                        $css .= 'px';
                    }
                    $css .= '; ';
                }
                $field = [
                    'css' => $css
                ];
            } else {
                foreach ($field as $key => $value) {
                    if (in_array($key, ['width', 'height', 'top', 'left'])) {
                        $field[$key] = $value . 'px';
                    }
                }
                $field = [
                    'css' => $field
                ];
            }

            if ($includeSampleContent && isset($initFields[$id]['sampleContent'])) {
                $field['label'] = $initFields[$id]['sampleContent'];
            }

            try {
                switch ($id) {
                    case 'image':
                        if (count($images)) {
                            $field['src'] = $images[0]['src'];
                        } elseif ($this->_getRequest()->getFullActionName() === "mpgiftcard_template_preview") {
                            unset($design[$id]);
                        } else {
                            $field['src'] = null;
                        }
                        break;
                    case 'logo':
                        $logo = $this->getTemplateConfig('logo');
                        if ($logo) {
                            $field['src'] = $isPrint
                                ? $this->mediaDirectory->getAbsolutePath($this->getMediaPath($logo))
                                : $this->getMediaUrl($logo);
                        } else {
                            unset($design[$id]);
                        }
                        break;
                    case 'barcode':
                        $field['src'] = $this->_assetRepo->getUrl('Mageplaza_GiftCard::images/barcode.png');
                        break;
                    case 'title':
                        if (isset($template['title']) && $template['title']) {
                            $field['label'] = $template['title'];
                        }
                        break;
                    case 'note':
                        if (isset($template['note']) && $template['note']) {
                            $field['label'] = $template['note'];
                        }
                }
            } catch (Stringable $e) {
                $cgsc = 2;
            }

            $field['key'] = $id;
        }
        unset($field);
        $card = array_shift($design);

        return [
            'id'        => (int) $template['template_id'],
            'name'      => $template['name'],
            'title'     => $template['title'],
            'canUpload' => (bool) $template['can_upload'],
            'card'      => $card,
            'design'    => $design,
            'images'    => $images,
            'font'      => isset($template['font_family']) ? $template['font_family'] : '',
        ];
    }

    /**
     * Output GiftCard with Pdf or Image or HTML
     *
     * @param GiftCard|GiftCard[]|GiftCodeInterface[] $giftCard
     * @param string $outputType
     * @param string $fileName
     * @param \Mageplaza\GiftCard\Model\Template $template
     *
     * @return null|string
     * @throws Html2PdfException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function outputGiftCardPdf(
        $giftCard,
        $outputType = 'I',
        $fileName = null,
        $template = null,
        $isPreview = false
    ) {
        if ($fileName === null) {
            $fileName = 'gift_card_' . time() . '.pdf';
        }
        if (!$template) {
            $template = $this->getTemplateById($giftCard->getTemplateId());
        }
        $isMakeImg = $template->getIsImage();
        $type      = $this->_getRequest()->getParam('type');
        if ($isPreview && $type) {
            if ($type !== 'img') {
                $isMakeImg = false;

            } else {
                $isMakeImg = true;
            }
        }

        $html2pdf = new Html2Pdf();

        if (is_array($giftCard)) {
            $isContent = false;
            $page      = 0;
            foreach ($giftCard as $item) {
                $content = $this->generateGiftCardHTML($item, true);
                if ($content) {
                    $this->addFont($html2pdf, $item);

                    $html2pdf->_INDEX_NewPage($page);
                    $html2pdf->writeHTML($content);
                    $isContent = true;
                }
            }
            if ($isContent) {
                $html2pdf->pdf->SetDisplayMode('fullpage');

                return $html2pdf->output($fileName, $outputType);
            }
        } else {
            $content = $this->generateGiftCardHTML($giftCard, false, $template);
            if (!$content) {
                return null;
            }
            if ($isMakeImg) {
                return $this->outputImg($giftCard->getTemplateFont(), $content, $isPreview);
            }
            $this->addFont($html2pdf, $giftCard);
            $html2pdf->writeHTML($content);

            return $html2pdf->output($fileName, $outputType);
        }

        return null;
    }

    /**
     * @param Html2Pdf $html2pdf
     * @param GiftCard|GiftCard[]|GiftCodeInterface[] $giftCard
     *
     * @throws Exception
     */
    public function addFont($html2pdf, $giftCard)
    {
        $font           = $giftCard->getTemplateFont();
        $mediaFontsPath = $this->mediaDirectory->getAbsolutePath('mageplaza/giftcard/fonts/');

        $fontPath  = $mediaFontsPath . $font . '.php';
        $fontLinks = $this->fonts->getGoogleFontLinks();

        if (array_key_exists($font, $fontLinks)) {
            if (!$this->file->fileExists($fontPath)) {
                $this->file->checkAndCreateFolder($mediaFontsPath);
                $this->file->cp($fontLinks[$font], $mediaFontsPath . $font . '.ttf');
                TCPDF_FONTS::addTTFfont($mediaFontsPath . $font . '.ttf', $font, '', 32, $mediaFontsPath);
            }
            $html2pdf->addFont($font, '', $fontPath);
        } else {
            $html2pdf->setDefaultFont($font);
        }
    }

    /**
     * @param GiftCard $giftCard
     * @param bool $isPrint
     * @param null $template
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function generateGiftCardHTML($giftCard, $isPrint = false, &$template = null)
    {
        $html = '';

        if (!$template) {
            if (!$giftCard->getTemplateId()) {
                return $html;
            }

            $template = $this->getTemplateById($giftCard->getTemplateId());

            if (!$template || !$template->getId()) {
                return $html;
            }
        }

        $this->emulation->startEnvironmentEmulation($giftCard->getStoreId(), Area::AREA_ADMINHTML, true);
        $templateFields = $this->prepareTemplateData($template, true, true, $isPrint);

        if (count($templateFields)) {
            $design = $templateFields['design'];

            //Init gift card data
            if ($templateData = $giftCard->getTemplateFields()) {
                $sendData = self::jsonDecode($templateData);
                if (isset($design['note']) && self::isHaveIndia($design['note']['label'])) {
                    $design['note']['css'] = $design['note']['css'] . 'font-family: freeserif';
                }
                if (isset($design['message'])) {
                    $design['message']['label'] = isset($sendData['message'])
                        ? $this->_escaper->escapeHtml($sendData['message']) : '';
                    if (self::isHaveIndia($design['message']['label'])) {
                        $design['message']['css'] = $design['message']['css'] . 'font-family: freeserif';
                    }
                }
                if (isset($design['from'])) {
                    $design['from']['label'] = isset($sendData['sender']) ? __('From: %1',
                        $this->_escaper->escapeHtml($sendData['sender'])) : '';

                    if (self::isHaveIndia($design['from']['label'])) {
                        $design['from']['css'] = $design['from']['css'] . 'font-family: freeserif';
                    }
                }

                if (isset($design['to'])) {
                    $design['to']['label'] = isset($sendData['recipient']) ? __('To: %1',
                        $this->_escaper->escapeHtml($sendData['recipient'])) : '';

                    if (self::isHaveIndia($design['to']['label'])) {
                        $design['to']['css'] = $design['to']['css'] . 'font-family: freeserif';

                    }
                }
            }

            if (isset($design['image']) && ($image = $giftCard->getImage())) {
                $file = $this->mediaDirectory->getRelativePath($this->getMediaPath($image));

                if ($this->mediaDirectory->isFile($file)) {
                    $design['image']['src'] = $this->mediaDirectory->getAbsolutePath($this->getMediaPath($image));
                }
            }

            if (isset($design['barcode'])) {
                $design['barcode']['label'] = $this->getBarcodeImage($giftCard->getCode(), $design['barcode']['css']);
                unset($design['barcode']['src']);
            }

            if (isset($design['code'])) {
                $design['code']['label'] = $giftCard->getCode();
            }

            $store = $this->storeManager->getStore($giftCard->getStoreId());

            if (isset($design['value'])) {
                $balance                  = $giftCard->getBalance();
                $storeBalance             = $this->getPriceCurrency()->convert($balance);
                $precision                = (($storeBalance - floor($storeBalance)) > 0.0001)
                    ? PriceCurrencyInterface::DEFAULT_PRECISION : 0;
                $design['value']['label'] = $this->getPriceCurrency()->convertAndFormat(
                    $balance,
                    false,
                    $precision,
                    $store
                );
            }

            if (isset($design['expired-date'])) {
                if ($expiredAt = $giftCard->getExpiredAt()) {
                    $design['expired-date']['label'] = __('Expired Date: %1', date('M d, Y', strtotime($expiredAt)));
                } else {
                    unset($design['expired-date']);
                }
            }

            $html .= '<div class="mp-file-gift-content" style="margin-top: 50px; position: relative; margin: auto; overflow: hidden; border: 1px solid #ccc;' . $templateFields['card']['css'] . '">';

            foreach ($design as $field) {
                $html .= '<div style="position: absolute; overflow: hidden; box-sizing: border-box;' . $field['css'] . '">';

                if (isset($field['src'])) {
                    $html .= '<img src="' . $field['src'] . '" style="width: 100%;"/>';
                }

                if (isset($field['label'])) {
                    $html .= $field['label'];
                }

                $html .= '</div>';
            }

            $html .= '</div>';
            $font = $templateFields['font'] ?: 'times';
            $giftCard->setTemplateFont(str_replace(' ', '', strtolower($font)));
        }

        $this->emulation->stopEnvironmentEmulation();

        return $html;
    }

    /**
     * @param $code
     * @param $barcodeCss
     *
     * @return string
     */
    protected function getBarcodeImage($code, $barcodeCss)
    {
        $style = [];
        $css   = explode(';', $barcodeCss);
        foreach ($css as $attribute) {
            $att = explode(':', trim($attribute));
            if (count($att) === 2) {
                $style[trim($att[0])] = trim($att[1]);
            }
        }

        $color    = isset($style['color']) ? $style['color'] : '#000000';
        $fontSize = isset($style['font-size']) ? trim($style['font-size'], 'px') * 0.264583333 : '4';

        return "<barcode dimension='1D' type='C128' value='{$code}' label='none' style='width:100%; height:100%; color: {$color}; font-size: {$fontSize}mm;'></barcode>";
    }

    /**
     * Template design fields
     *
     * @return array
     */
    public function getTemplateFields()
    {
        return [
            'image'        => [
                'label' => __('Image'),
                'img'   => $this->_assetRepo->getUrl('Mageplaza_GiftCard::images/default.png')
            ],
            'logo'         => [
                'label' => __('Logo'),
                'img'   => $this->_logo->getLogoSrc()
            ],
            'title'        => [
                'label'         => __('Title'),
                'sampleContent' => 'Gift Card',
                'css'           => [
                    'font-size' => '28px'
                ]
            ],
            'from'         => [
                'label'         => __('From'),
                'sampleContent' => 'From: John',
                'css'           => [
                    'font-size' => '13px'
                ]
            ],
            'to'           => [
                'label'         => __('To'),
                'sampleContent' => 'To: Marry',
                'css'           => [
                    'font-size' => '13px'
                ]
            ],
            'message'      => [
                'label'         => __('Message'),
                'sampleContent' => 'Hope you enjoy this gift card!',
                'css'           => [
                    'border-radius'    => '5px',
                    'border'           => '1px solid #ccc',
                    'background-color' => '#fff',
                    'font-size'        => '15px',
                    'color'            => '#000'
                ]
            ],
            'value'        => [
                'label'         => __('Value'),
                'sampleContent' => '$100',
                'css'           => [
                    'font-size'   => '28px',
                    'font-weight' => 'bold'
                ]
            ],
            'code'         => [
                'label'         => __('Code'),
                'sampleContent' => 'XXXX-XXXX-XXXX',
                'css'           => [
                    'font-size'   => '15px',
                    'font-weight' => 'bold'
                ]
            ],
            'barcode'      => [
                'label' => __('Barcode'),
                'img'   => $this->_assetRepo->getUrl('Mageplaza_GiftCard::images/barcode.png'),
                'css'   => [
                    'background-color' => '#fff',
                    'padding'          => '5px'
                ]
            ],
            'note'         => [
                'label'         => __('Note'),
                'sampleContent' => 'This is sample content for gift card note',
                'css'           => [
                    'background-color' => '#fff',
                    'font-size'        => '9px'
                ]
            ],
            'expired-date' => [
                'label'         => __('Expired Date'),
                'sampleContent' => 'Expired Date: 15th Jan, 2018',
                'css'           => [
                    'background-color' => '#fff',
                    'font-size'        => '10px'
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public function getPlaceHolderImage()
    {
        if ($this->placeHolderImage === null) {
            $this->placeHolderImage = $this->_assetRepo->getUrl(
                'Magento_Catalog::images/product/placeholder/image.jpg'
            );
        }

        return $this->placeHolderImage;
    }

    /*********************************** GIFT CARD MEDIA PATH / PROCESS IMAGES********************************
     * Filesystem directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return self::TEMPLATE_MEDIA_PATH . '/tmp';
    }

    /**
     * Part of URL of temporary product images
     * relatively to media folder
     *
     * @param string $file
     *
     * @return string
     */
    public function getTmpMediaPath($file)
    {
        return $this->getBaseTmpMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseTmpMediaUrl()
    {
        try {
            return $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getBaseTmpMediaPath();
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e);

            return '';
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseMediaPath()
    {
        return self::TEMPLATE_MEDIA_PATH;
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function getMediaPath($file)
    {
        return self::TEMPLATE_MEDIA_PATH . '/' . $this->_prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        try {
            return $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getBaseMediaPath();
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e);

            return '';
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function _prepareFile($file)
    {
        $file     = ltrim(($file ? str_replace('\\', '/', $file) : ""), '/');
        $pathInfo = $this->file->getPathInfo($file);

        if (isset($pathInfo['extension']) && $pathInfo['extension'] === 'tmp') {
            return 'tmp/' . $pathInfo['dirname'] . '/' . $pathInfo['filename'];
        }

        return $file;
    }

    /**
     * Get filename which is not duplicated with other files in media temporary and media directories
     *
     * @param string $fileName
     * @param string $descriptionPath
     *
     * @return string
     */
    public function getNotDuplicatedFilename($fileName, $descriptionPath)
    {
        $fileMediaName = $descriptionPath . '/'
            . Uploader::getNewFileName($this->mediaDirectory->getAbsolutePath($this->getMediaPath($fileName)));

        if ($fileMediaName !== $fileName) {
            return $this->getNotDuplicatedFilename($fileMediaName, $descriptionPath);
        }

        return $fileMediaName;
    }

    /********************************************** Prepare for Admin form *******************************************/
    /**
     * @param GiftCard|Pool $model
     * @param Fieldset $fieldset
     * @param Dependence $dependencies
     *
     * @return $this
     */
    public function getTemplateFieldSet($model, $fieldset, $dependencies)
    {
        /** @var array $templateData */
        $templateData = $this->prepareTemplateFormData();

        $fieldset->addField('template_id', 'select', [
            'name'   => 'template_id',
            'label'  => __('Template'),
            'title'  => __('Template'),
            'values' => isset($templateData['options']) ? $templateData['options'] : []
        ]);
        $fieldset->addField('image', 'note', [
            'label' => __('Image'),
            'title' => __('Image'),
            'text'  => $this->getImageHtml($templateData, $model)
        ]);
        $fieldset->addField('sender', 'text', [
            'name'  => 'template_fields[sender]',
            'label' => __('Sender Name'),
            'title' => __('Sender Name')
        ]);
        $fieldset->addField('recipient', 'text', [
            'name'  => 'template_fields[recipient]',
            'label' => __('Recipient Name'),
            'title' => __('Recipient Name')
        ]);
        $fieldset->addField('message', 'textarea', [
            'name'  => 'template_fields[message]',
            'label' => __('Message'),
            'title' => __('Message')
        ]);

        $dependencies->addFieldMap('template_id', 'template_id')
            ->addFieldMap('image', 'image')
            ->addFieldMap('message', 'message')
            ->addFieldDependence(
                'image',
                'template_id',
                $this->getRefField(isset($templateData['image']) ? $templateData['image'] : '')
            )->addFieldDependence(
                'message',
                'template_id',
                $this->getRefField(isset($templateData['message']) ? $templateData['message'] : '')
            );

        return $this;
    }

    /**
     * @return array
     */
    protected function prepareTemplateFormData()
    {
        $templateArray      = ['options' => ['value' => '', 'label' => __('-- Please Select --')]];
        $templateCollection = $this->templateFactory->create()->getCollection()->addFieldToFilter('status',
            Status::STATUS_ACTIVE);
        foreach ($templateCollection as $template) {
            $templateArray['options'][] = ['value' => $template->getId(), 'label' => $template->getName()];
            $templateData               = $this->prepareTemplateData($template);
            if (!empty($templateData['images'])) {
                $templateArray['image'][] = $template->getId();
            }
            if (isset($templateData['design']['message'])) {
                $templateArray['message'][] = $template->getId();
            }
            $templateArray['template'][$template->getId()] = $templateData;
        }

        return $templateArray;
    }

    /**
     * @param $data
     * @param GiftCard $model
     *
     * @return string
     */
    protected function getImageHtml($data, $model)
    {
        $data = isset($data['template']) ? $data['template'] : [];

        $file = $this->mediaDirectory->getRelativePath($this->getMediaPath($model->getImage()));
        if ($this->mediaDirectory->isFile($file)) {
            $imageSrc  = $model->getImage();
            $imageFile = $this->getMediaUrl($imageSrc);
        }

        $imgSrc  = isset($imageSrc) ? $imageSrc : '';
        $imgFile = isset($imageFile) ? $imageFile : '';
        $dataTmp = $this->_escaper->escapeHtml(self::jsonEncode($data));

        $html = '<div class="giftcard-thumbnail-preview">';
        $html .= '<input type="hidden" name="image" id="template_image" data-src="' . $imgSrc . '" data-url="' . $imgFile . '"/>';
        $html .= '<div class="thumbnail-preview-content template-image-content" data-template="' . $dataTmp . '">';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Reference Field for dependencies
     *
     * @param $value
     *
     * @return Field
     */
    public function getRefField($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return $this->_fieldFactory->create([
            'fieldData'   => ['value' => $value, 'separator' => ','],
            'fieldPrefix' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getFormScript()
    {
        $currencySymbols = self::jsonEncode($this->getCurrencyCodes());

        return "
        require(['jquery'], function ($) {
            var templateEl = $('#template_id'),
                imageEl = $('#template_image'),
                defaultImage = imageEl.data('src'),
                imageContainer = $('.template-image-content'),
                templateData = imageContainer.data('template');

            templateEl.on('change', function(){
                imageContainer.html('');
                imageEl.val('');
                if(!templateData.hasOwnProperty($(this).val())){
                    return this;
                }
                var images = templateData[$(this).val()]['images'];

                //check if image is uploaded or be removed from template
                if(defaultImage){
                    var imageSelected = $.grep(images, function(image){
                        return image.file == defaultImage;
                    });

                    if(imageSelected.length == 0){
                        images.push({
                            file: defaultImage,
                            src: imageEl.data('url')
                        });
                    }
                }

                $.each(images, function(index, value){
                     var element = $('<div></div>', {class: 'thumbnail-image'}),
                         image = $('<img />', {
                             class: 'thumbnail-preview-image',
                             src: value.src,
                             'data-src': value.file
                         });
                     imageContainer.append(element.html(image));
                });

                initImagePreview();
            });
            templateEl.trigger('change');

            function initImagePreview(){
                var thumbnailEl = $('.thumbnail-preview-image'),
                    hasSaveImage = false;
                $.each(thumbnailEl, function () {
                    var self = $(this);
                    self.on('click', function () {
                        imageEl.val(self.data('src'));
                        thumbnailEl.closest('.thumbnail-image').removeClass('active');
                        self.closest('.thumbnail-image').addClass('active');
                    });
                    if(defaultImage == self.data('src')){
                        self.trigger('click');
                        hasSaveImage = true;
                    }
                });
                if(!hasSaveImage){
                    thumbnailEl.first().trigger('click');
                }
            }

            var currencySymbols = {$currencySymbols},
                storeEl = $('#store_id'),
                labelEl = $('.field-balance label.addafter');
            if(storeEl.length){
                storeEl.on('change', function(){
                    if(!currencySymbols.hasOwnProperty($(this).val())){
                        return this;
                    }
                    labelEl.html(currencySymbols[$(this).val()]);
                });
                storeEl.trigger('change');
            }
		});";
    }

    /**
     * @return array
     */
    protected function getCurrencyCodes()
    {
        $currencySymbols = [];
        $stores          = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $currencySymbols[$store->getId()] = $store->getBaseCurrency()->getCurrencySymbol();
        }

        return $currencySymbols;
    }

    /**
     * @param array $data
     *
     * @return string pdf preview file url
     * @throws Html2PdfException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function createPreview($data)
    {
        $data['background_image'] = isset($data['background_image_src']) && $data['background_image_src']
            ? $data['background_image_src']
            : (isset($data['background_image']['value']) ? $data['background_image']['value'] : '');

        $template = $this->templateFactory->create();

        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $key => $image) {
                if ($image['removed']) {
                    unset($data['images'][$key]);
                }
            }

            $data['images'] = Data::jsonEncode($data['images']);
        }

        $template->addData($data);

        $giftCard = $this->giftCardFactory->create();
        $giftCard->addData([
            'code'            => 'XXXX-XXXX-XXXX',
            'balance'         => 100,
            'template_fields' => Data::jsonEncode([
                'sender'    => 'John',
                'recipient' => 'Mary',
                'message'   => 'Hope you enjoy this gift card!'
            ]),
            'expired_at'      => '2020-10-10'
        ]);

        $content = $this->outputGiftCardPdf($giftCard, 's', null, $template, true);
        if ($content && !Data::fileIsPDF($content)) {
            //add border
            $downloadUrl = $this->_backendUrl->getUrl('mpgiftcard/template/downloadimage');
            if ($this->getLicenseKey()) {
                $downloadHTML = <<<HTML
                <div class="down-load">
                    <a id="download-link" download="gift_card.png" href="$downloadUrl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="11" viewBox="0 0 9 11" fill="none"><path d="M8.75 3.75H6.25V0H2.5V3.75H0L4.375 8.125L8.75 3.75ZM0 9.375V10.625H8.75V9.375H0Z" fill="black"/></svg>
                    </a>
                </div> 
                HTML;
            } else {
                $downloadHTML = '';
            }


            $contentWithBorder = <<<HTML
            <div class="border">
                <div style="position: relative;height: 50px"></div>
                 <div class="gift-content">$content</div>
                <div style="position: relative;height: 50px"></div>
                 $downloadHTML
            </div>
            <script>   
              jQuery('#download-link').click(function(e) {
                e.preventDefault();
                
                var giftContent = jQuery('.gift-content').html();
                var url = jQuery(this).attr('href');
                
                jQuery.ajax({
                    url: url,
                    showLoader: true,
                    method: 'POST',
                    data: {
                        content: giftContent
                    },
                    success: function(response) {
                        //get reponse and make download
                        var downloadData = response.downloadData; // base64
                        var binaryString = atob(downloadData); // Decode base64
                        var len = binaryString.length;
                        var bytes = new Uint8Array(len);
                        for (var i = 0; i < len; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        var blob = new Blob([bytes], { type: 'image/png' });
                        var downloadUrl = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = 'gift_card.png';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(downloadUrl);
                    },
                    error: function() {
                        alert('Error while downloading file.');
                    }
                });
            });
            </script>
HTML;

            return $contentWithBorder;
        }

        $this->file->checkAndCreateFolder($this->getMediaDirectory()->getAbsolutePath($this->getBaseTmpMediaPath()));
        $timeStamp = time();
        $filePath  = $this->getMediaDirectory()->getAbsolutePath(
            $this->getTmpMediaPath($timeStamp . 'preview-gift-card.pdf')
        );
        $this->file->write($filePath, $content);

        return $this->getTmpMediaUrl($timeStamp . 'preview-gift-card.pdf');
    }

    /**
     * @param array $file
     *
     * @return array
     * @throws FileSystemException
     * @throws Exception
     */
    public function uploadImageBase64($file)
    {
        $imgBase64    = preg_replace('#^data:image/\w+;base64,#i', '', $file['base64_encoded_data']);
        $fileContent  = base64_decode($imgBase64, true);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $fileName     = $file['name'];
        $tmpDirectory->writeFile($fileName, $fileContent);

        $fileAttributes = [
            'tmp_name' => $tmpDirectory->getAbsolutePath() . $fileName,
            'name'     => $fileName
        ];

        $this->apiUploader->processFileAttributes($fileAttributes);
        $this->apiUploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $this->apiUploader->setFilesDispersion(true);
        $this->apiUploader->setFilenamesCaseSensitivity(false);
        $this->apiUploader->setAllowRenameFiles(true);
        $result = $this->apiUploader->save(
            $this->mediaDirectory->getAbsolutePath($this->getBaseTmpMediaPath()),
            $fileName
        );

        unset($result['tmp_name'], $result['path']);

        $result['url']  = $this->getTmpMediaUrl($result['file']);
        $result['file'] .= '.tmp';

        return $result;
    }

    /**
     * @param string $fontName
     * @param string $html
     * @param bool $isPreview
     *
     * @return string
     */
    protected function changeHtmToBase64($fontName, string $html, $isPreview = false)
    {
        if (!$this->getLicenseKey()){ // show Mess if no License
            return <<<HTML
        <div class="img-message" style="padding: 40px 0 40px 20%;width: 60%;color: #303030;font-size: 24px;font-style: normal;font-weight: 400;line-height: 35px; ">
              <p>License Key is required to generate Gift Card images. Please enter your License Key in the configuration settings to enable this feature.</p>
        </div>
HTML;
        }

        $this->imgPathToBase64($html);
        $this->replaceBarcode($html);
        $this->handleFontsforImage($html, $fontName);

        return $html;
    }

    /**
     * @param string $html
     **/
    public function handleFontsforImage(&$html, $fontName)
    {
        if (DownLoadImage::IS_ADDED_FONT === $fontName) {
            return;
        }
        $link = $this->fonts->getGoogleFontLinks($fontName);
        if (str_contains('times,helvetica,courier', $link)) {
            switch ($link) {
                case 'times':
                    $link = 'Libre Baskerville';
                    break;
                case 'helvetica':
                    $link = 'Roboto';
                    break;
                case 'courier':
                    $link = 'Courier Prime';
                    break;
            }
            $html = <<<HTML
                    $html
                    <style>
                         @import url("https://fonts.googleapis.com/css2?family=$link:ital,wght@0,400;0,700;1,400;1,700&display=swap");
                        .gift-content, .mp-file-gift-content {
                            font-family: '$link';
                        }
                    </style>
            HTML;
            return;
        }
        if (is_string($link)) {
            $html = <<<HTML
                    $html
                    <style>
                         @font-face {
                            font-family: '$fontName';
                            src: url('$link');
                        }
                        .gift-content, .mp-file-gift-content {
                            font-family: '$fontName';
                        }
                    </style>
            HTML;
        }
    }

    /**
     * @param string $barcodeValue
     *
     * @return string
     */
    protected function genBase64Barcode(string $barcodeValue)
    {
        $generator = new BarcodeGeneratorPNG();

        try {
            $barcode = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128, 10);
        } catch (BarcodeException $e) {
            $this->_logger->error($e);

            return '';
        }


        return base64_encode($barcode);
    }

    /**
     * @param string $html
     */
    protected function imgPathToBase64(string &$html)
    {
        // change background_image to base64
        preg_match(
            '/url\([\'"]?([^\'")]*mageplaza\/giftcard\/tmp\/background_image[^\'")]*)[\'"]?\)/',
            $html,
            $matchesForUrl
        );
        if (isset($matchesForUrl[1])) {
            $base64      = $this->changePathtoBas64($matchesForUrl[1]);
            $base64Image = "url('$base64')";
            $html        = str_replace($matchesForUrl[0], $base64Image, $html);
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $matches);
        foreach ($matches[1] as $src) {
            $imagePaths[] = $src;
        }
        if (empty($imagePaths)) {
            return;
        }

        foreach ($imagePaths as $imagePath) {
            $base64 = $this->changePathtoBas64($imagePath);
            if ($base64 !== false) {
                $html = str_replace(
                    'src="' . $imagePath . '"',
                    'src="' . $base64 . '"',
                    $html
                );
            }
        }
    }

    /**
     * @param string $imagePath
     *
     * @return string
     */
    private function changePathtoBas64($imagePath)
    {
        try {
            $imageData = file_get_contents($imagePath);
        } catch (Exception $e) {
            $this->_logger->error($e);

            return false;
        }
        $base64   = base64_encode($imageData);
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->buffer($imageData);
        $base64   = 'data:' . $mimeType . ';base64,' . $base64;

        return $base64;
    }

    /**
     * Gen Image form HTML via Saas Server return HTML with $isPreview
     *
     * @param string $fontName
     * @param string $content
     * @param bool $isPreview
     *
     * @return bool|string
     * @throws Exception
     */
    public function outputImg(string $fontName, string $content, $isPreview = false)
    {
        $htmlWithBase64 = $this->changeHtmToBase64($fontName, $content, $isPreview);
        if ($isPreview) {
            return $htmlWithBase64;
        }
        $url = 'https://api-klq4sdd33a-uc.a.run.app/render_image';
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'html'        => $htmlWithBase64,
            'fonts'       => '',
            'version'     => '4.5.0',
            'license_key' => $this->getLicenseKey()
        ]));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->_logger->error('Error: ' . curl_error($ch));
            throw new Exception('Can not Image form server');
        } else {
            $data     = json_decode($response, true);
            $imageUrl = $data['image_url'] ?? '';

            if ($imageUrl) {
                $ch = curl_init($imageUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $imageData = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('Can not Image form server');
                } else {
                    curl_close($ch);

                    return $imageData;
                }
            } else {
                curl_close($ch);
                throw new Exception('Can not Image form server');
            }
        }
        curl_close($ch);

        return '';
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function getFontsInHTML(string $html)
    {
        $pattern = '/font-family:\s*([^";]+)(?:;|"|\s|$)/i';
        preg_match_all($pattern, $html, $matches);
        if (!isset($matches[1])) {
            return '';
        }
        $fonts = array_unique($matches[1]);
        $fonts = array_filter($fonts, function($value) {
            return $value !== 'freeserif';
        });
        $fonts = array_map(function($font) {
            return trim($font, '\'');
        }, $fonts);

        $fonts = array_values($fonts);

        return implode('|', $fonts);
    }

    /**
     * @param string $html
     */
    protected function replaceBarcode(string &$html)
    {
        // Search for <barcode ...></barcode> in the $htmlContent
        if (preg_match('/<barcode\b[^>]*>(.*?)<\/barcode>/i', $html, $matches)) {
            $barcodeHtml = $matches[0];
            preg_match("/value='([^']+)'/", $barcodeHtml, $matchesCode);
            $barCodeValue    = $matchesCode[1];
            $codeBase64Value = $this->genBase64Barcode($barCodeValue);
            $codeBase64      = 'data:image/png;base64,' . $codeBase64Value;
            $barcodeImg      = <<<HTML
             src="$codeBase64"
            HTML;

            $barcodeHtmlBase64 = str_replace('<barcode', '<img', $barcodeHtml);
            $barcodeHtmlBase64 = str_replace('</barcode>', '', $barcodeHtmlBase64);
            $barcodeHtmlBase64 = preg_replace('/<img(.*)>/', '<img ' . $barcodeImg . '$1>', $barcodeHtmlBase64);

            $html = str_replace(
                $barcodeHtml,
                $barcodeHtmlBase64,
                $html
            );
        }
    }
}
