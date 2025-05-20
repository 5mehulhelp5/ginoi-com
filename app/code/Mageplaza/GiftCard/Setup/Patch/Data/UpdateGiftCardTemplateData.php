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

namespace Mageplaza\GiftCard\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateGiftCardTemplateData
 * @package Mageplaza\SeoAnalysis\Setup\Patch\Data
 */

class UpdateGiftCardTemplateData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        if ($connection->isTableExists('mageplaza_giftcard_template')) {
            try {
                $select = $connection->select()
                    ->from('mageplaza_giftcard_template', ['template_id', 'design']);
                $rows = $connection->fetchAll($select);

                foreach ($rows as $row) {
                    $updatedDesign = $this->updateDesign($row['design']);

                    $connection->update(
                        'mageplaza_giftcard_template',
                        ['design' => $updatedDesign],
                        ['template_id = ?' => $row['template_id']]
                    );
                }

                $this->logger->info('Mageplaza GiftCard template data updated successfully.');
            } catch (\Exception $e) {
                $this->logger->error('Error updating Mageplaza GiftCard template data: ' . $e->getMessage());
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Function to update design data
     *
     * @param string $design
     * @return string
     */
    private function updateDesign($design)
    {
        $designData = json_decode($design, true);

        // New data to insert
        $newData = [
            "backgroundColor" => "transparent",
            "border" => "solid",
            "borderColor" => "transparent",
            "borderWidth" => 0,
            "borderRadius" => 0,
            "align" => "left",
            "bold" => "",
            "italic" => "",
            "underline" => "",
            "textColor" => "#fff",
            "fontSize" => 0
        ];

        // Insert new data after 'height' in each relevant section
        foreach ($designData as &$section) {
            if (isset($section['height'])) {
                $section = array_merge(
                    array_slice($section, 0, array_search('height', array_keys($section)) + 1),
                    $newData,
                    array_slice($section, array_search('height', array_keys($section)) + 1)
                );
            }
            // Update section values based on 'css'
            if (isset($section['css'])) {
                if (isset($section['css']['font-size'])) {
                    $section['fontSize'] = (int)filter_var($section['css']['font-size'], FILTER_SANITIZE_NUMBER_INT);
                }
                if (isset($section['css']['background-color'])) {
                    $section['backgroundColor'] = $section['css']['background-color'];
                }
                if (isset($section['css']['font-weight'])) {
                    $section['bold'] = $section['css']['font-weight'];
                }
                if (isset($section['css']['color'])) {
                    $section['textColor'] = $section['css']['color'];
                }
            }
        }

        return json_encode($designData);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
