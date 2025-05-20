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

namespace Mageplaza\GiftCard\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Fonts
 * @package Mageplaza\GiftCard\Model\Source
 */
class Fonts implements ArrayInterface
{
    const ROBOTO                  = 'Roboto';
    const OPEN_SANS               = 'Open Sans';
    const LATO                    = 'Lato';
    const MONTSERRAT              = 'Montserrat';
    const ROBOTO_CONDENSED        = 'Roboto Condensed';
    const SOURCE_SANS_PRO         = 'Source Sans Pro';
    const OSWALD                  = 'Oswald';
    const RALEWAY                 = 'Raleway';
    const SLABO_27PX              = 'Slabo 27px';
    const PT_SANS                 = 'PT Sans';
    const MERRIWEATHER            = 'Merriweather';
    const ROBOTO_SLAB             = 'Roboto Slab';
    const OPEN_SANS_CONDENSED_300 = 'Open Sans Condensed';
    const UBUNTU                  = 'Ubuntu';
    const NOTO_SANS               = 'Noto Sans';
    const POPPINS                 = 'Poppins';
    const ROBOTO_MONO             = 'Roboto Mono';
    const PLAYFAIR_DISPLAY        = 'Playfair Display';
    const LORA                    = 'Lora';
    const PT_SERIF                = 'PT Serif';
    const TITILLIUM_WEB           = 'Titillium Web';
    const MULI                    = 'Mulish';
    const ARIMO                   = 'Arimo';
    const FIRA_SANS               = 'Fira Sans';
    const PT_SANS_NARROW          = 'PT Sans Narrow';
    const NUNITO                  = 'Nunito';
    const NOTO_SERIF              = 'Noto Serif';
    const INCONSOLATA             = 'Inconsolata';
    const NANUM_GOTHIC            = 'Nanum Gothic';
    const CRIMSON_TEXT            = 'Crimson Text';

    /**
     * @return array
     */
    public function getGoogleFonts()
    {
        return [
            [
                'value' => self::ROBOTO,
                'label' => __('Roboto'),
                'link'  => 'https://fonts.gstatic.com/s/risque/v8/VdGfAZUfHosahXxoCUYVBJ-T5g.ttf'
            ],
            [
                'value' => self::OPEN_SANS,
                'label' => __('Open+Sans'),
                'link'  => 'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-U1UpcaXcl0Aw.ttf'
            ],
            [
                'value' => self::LATO,
                'label' => __('Lato'),
                'link'  => 'https://fonts.gstatic.com/s/lato/v17/S6uyw4BMUTPHvxk6XweuBCY.ttf'
            ],
            [
                'value' => self::MONTSERRAT,
                'label' => __('Montserrat'),
                'link'  => 'https://fonts.gstatic.com/s/montserrat/v15/JTUSjIg1_i6t8kCHKm45xW5rygbi49c.ttf'
            ],
            [
                'value' => self::ROBOTO_CONDENSED,
                'label' => __('Roboto+Condensed'),
                'link'  => 'https://fonts.gstatic.com/s/robotocondensed/v19/ieVl2ZhZI2eCN5jzbjEETS9weq8-59WxDCs5cvI.ttf'
            ],
            [
                'value' => self::SOURCE_SANS_PRO,
                'label' => __('Source+Sans+Pro'),
                'link'  => 'https://fonts.gstatic.com/s/sourcesanspro/v14/6xK3dSBYKcSV-LCoeQqfX1RYOo3aP6TkmDZz9g.ttf'
            ],
            [
                'value' => self::OSWALD,
                'label' => __('Oswald'),
                'link'  => 'https://fonts.gstatic.com/s/oswald/v35/TK3_WkUHHAIjg75cFRf3bXL8LICs1_FvgUFoZAaRliE.ttf'
            ],
            [
                'value' => self::RALEWAY,
                'label' => __('Raleway'),
                'link'  => 'https://fonts.gstatic.com/s/raleway/v18/1Ptxg8zYS_SKggPN4iEgvnHyvveLxVvaooCPNLA3JC9c.ttf'
            ],
            [
                'value' => self::SLABO_27PX,
                'label' => __('Slabo+27px'),
                'link'  => 'https://fonts.gstatic.com/s/slabo27px/v7/mFT0WbgBwKPR_Z4hGN2qsxgJ1EJ7i90.ttf'
            ],
            [
                'value' => self::PT_SANS,
                'label' => __('PT+Sans'),
                'link'  => 'https://fonts.gstatic.com/s/ptsans/v12/jizaRExUiTo99u79P0WOxOGMMDQ.ttf'
            ],
            [
                'value' => self::MERRIWEATHER,
                'label' => __('Merriweather'),
                'link'  => 'https://fonts.gstatic.com/s/merriweather/v22/u-440qyriQwlOrhSvowK_l5OeyxNV-bnrw.ttf'
            ],
            [
                'value' => self::ROBOTO_SLAB,
                'label' => __('Roboto+Slab'),
                'link'  => 'https://fonts.gstatic.com/s/robotoslab/v12/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISWaG5iddG-1A.ttf'
            ],
            [
                'value' => self::OPEN_SANS_CONDENSED_300,
                'label' => __('Open+Sans+Condensed:300'),
                'link'  => 'https://fonts.gstatic.com/s/opensanscondensed/v15/z7NFdQDnbTkabZAIOl9il_O6KJj73e7Ff1GhPuLGRpWRyAs.ttf'
            ],
            [
                'value' => self::UBUNTU,
                'label' => __('Ubuntu'),
                'link'  => 'https://fonts.gstatic.com/s/ubuntu/v15/4iCs6KVjbNBYlgo6eAT3v02QFg.ttf'
            ],
            [
                'value' => self::NOTO_SANS,
                'label' => __('Noto+Sans'),
                'link'  => 'https://fonts.gstatic.com/s/notosans/v11/o-0IIpQlx3QUlC5A4PNb4j5Ba_2c7A.ttf'
            ],
            [
                'value' => self::POPPINS,
                'label' => __('Poppins'),
                'link'  => 'https://fonts.gstatic.com/s/poppins/v13/pxiEyp8kv8JHgFVrFJDUc1NECPY.ttf'
            ],
            [
                'value' => self::ROBOTO_MONO,
                'label' => __('Roboto+Mono'),
                'link'  => 'https://fonts.gstatic.com/s/robotomono/v12/L0xuDF4xlVMF-BfR8bXMIhJHg45mwgGEFl0_3vqPQ--5Ip2sSQ.ttf'
            ],
            [
                'value' => self::PLAYFAIR_DISPLAY,
                'label' => __('Playfair+Display'),
                'link'  => 'https://fonts.gstatic.com/s/playfairdisplay/v21/nuFvD-vYSZviVYUb_rj3ij__anPXJzDwcbmjWBN2PKdFvUDQZNLo_U2r.ttf'

            ],
            [
                'value' => self::LORA,
                'label' => __('Lora'),
                'link'  => 'https://fonts.gstatic.com/s/lora/v16/0QI6MX1D_JOuGQbT0gvTJPa787weuyJGmKxemMeZ.ttf'
            ],
            [
                'value' => self::PT_SERIF,
                'label' => __('PT+Serif'),
                'link'  => 'https://fonts.gstatic.com/s/ptserif/v12/EJRVQgYoZZY2vCFuvDFRxL6ddjb-.ttf'
            ],
            [
                'value' => self::TITILLIUM_WEB,
                'label' => __('Titillium+Web'),
                'link'  => 'https://fonts.gstatic.com/s/titilliumweb/v9/NaPecZTIAOhVxoMyOr9n_E7fRMTsDIRSfr0.ttf'
            ],
            [
                'value' => self::MULI,
                'label' => __('Mulish'),
                'link'  => 'https://fonts.gstatic.com/s/mulish/v1/1Ptyg83HX_SGhgqO0yLcmjzUAuWexZNRwaClGrw-PTY.ttf'
            ],
            [
                'value' => self::ARIMO,
                'label' => __('Arimo'),
                'link'  => 'https://fonts.gstatic.com/s/arimo/v15/P5sMzZCDf9_T_20eziBMjI-u.ttf'
            ],
            [
                'value' => self::FIRA_SANS,
                'label' => __('Fira+Sans'),
                'link'  => 'https://fonts.gstatic.com/s/firasans/v10/va9E4kDNxMZdWfMOD5VfkILKSTbndQ.ttf'
            ],
            [
                'value' => self::PT_SANS_NARROW,
                'label' => __('PT+Sans+Narrow'),
                'link'  => 'https://fonts.gstatic.com/s/ptsansnarrow/v12/BngRUXNadjH0qYEzV7ab-oWlsYCByxyKeuDp.ttf'
            ],
            [
                'value' => self::NUNITO,
                'label' => __('Nunito'),
                'link'  => 'https://fonts.gstatic.com/s/nunito/v14/XRXV3I6Li01BKof4MuyAbsrVcA.ttf'
            ],
            [
                'value' => self::NOTO_SERIF,
                'label' => __('Noto+Serif'),
                'link'  => 'https://fonts.gstatic.com/s/notoserif/v9/ga6Iaw1J5X9T9RW6j9bNTFAcaRi_bMQ.ttf'
            ],
            [
                'value' => self::INCONSOLATA,
                'label' => __('Inconsolata'),
                'link'  => 'https://fonts.gstatic.com/s/inconsolata/v20/QldgNThLqRwH-OJ1UHjlKENVzkWGVkL3GZQmAwLYxYWI2qfdm7Lpp4U8aRr8lleY2co.ttf'
            ],
            [
                'value' => self::NANUM_GOTHIC,
                'label' => __('Nanum+Gothic'),
                'link'  => 'https://fonts.gstatic.com/s/nanumgothic/v17/PN_3Rfi-oW3hYwmKDpxS7F_z_tLfxno73g.ttf'
            ],
            [
                'value' => self::CRIMSON_TEXT,
                'label' => __('Crimson+Text'),
                'link'  => 'https://fonts.gstatic.com/s/crimsontext/v11/wlp2gwHKFkZgtmSR3NB0oRJvaAJSA_JN3Q.ttf'
            ],
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $fonts = [
            ['value' => 'times', 'label' => 'Times-Roman'],
            ['value' => 'helvetica', 'label' => 'Helvetica'],
            ['value' => 'courier', 'label' => 'Courier']
        ];

        $result = array_merge($fonts, $this->getGoogleFonts());

        usort($result, function ($a, $b) {
            return ($a['label'] <= $b['label']) ? -1 : 1;
        });

        return $result;
    }

    /**
     * GetGoogleFontLinks with $value is fonts key
     *
     * @param string $value
     *
     * @return array|string
     */
    public function getGoogleFontLinks($value = null)
    {
        $result = [];
        $font   = '';
        foreach ($this->getGoogleFonts() as $googleFont) {
            $key = (string) str_replace(' ', '', strtolower($googleFont['value']));
            if ($value === $key) {
                $font = $googleFont['link'];
            }
            $result[$key] = $googleFont['link'];
        }

        if ($value) {
            if ($font) {
                return $font;
            } elseif (str_contains('times,helvetica,courier', $value)) {
                return $value ;
            }
        }

        return $result;
    }

    /**
     * Use to render Image
     *
     * @param string $valueFonts
     *
     * @return string
     */
    private function getLinkOfDefaultPDF(string $valueFonts)
    {
        switch ($valueFonts) {
            case 'times':
                return 'https://fonts.gstatic.com/s/librebaskerville/v14/kmKnZrc3Hgbbcjq75U4uslyuy4kn0qNXaxMICA.woff2';
            case 'helvetica':
                return 'https://fonts.gstatic.com/s/sourcecodepro/v23/HI_SiYsKILxRpg3hIP6sJ7fM7PqlPuvWjMY.woff2';
            case 'courier':
                return 'https://fonts.gstatic.com/s/courierprime/v9/u-450q2lgwslOqpF_6gQ8kELawFpWg.woff2';
            default:
                return '';
        }
    }
}
