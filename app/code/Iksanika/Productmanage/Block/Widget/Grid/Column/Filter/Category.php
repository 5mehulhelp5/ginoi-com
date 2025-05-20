<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Filter;

/**
 * Text grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Category extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    function getCondition()
    {
        if(trim($this->getValue())=='')
            return null;
        $categoryIds         =   explode(',', $this->getValue() ?? '');
        $categoryIdsArray    =   array();
        $catId = -1;
        foreach($categoryIds as $catId)
            $categoryIdsArray[] = trim($catId);

        if(count($categoryIdsArray) == 1 && (int)$catId == 0)
        {
            return array('null' => true);
        }else
        {
            return array('inset' => $categoryIdsArray);
        }
    }
    
}
