<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Copyright of customization © 2015 Iksanika. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

/**
 * Grid column widget for rendering grid cells that contains mapped values
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * Prepares action data for html render
     *
     * @param &array $action
     * @param &string $actionCaption
     * @param \Magento\Framework\DataObject $row
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _transformActionData(&$action, &$actionCaption, \Magento\Framework\DataObject $row)
    {

        foreach ($action as $attribute => $value) {
            if (isset($action[$attribute]) && !is_array($action[$attribute])) {
                $this->getColumn()->setFormat($action[$attribute]);
                $action[$attribute] = \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text::render($row);
            } else {
                $this->getColumn()->setFormat(null);
            }


            switch ($attribute) {
                case 'caption':
                    $actionCaption = $action['caption'];
                    unset($action['caption']);
                    break;

                case 'url':
                    if (is_array($action['url']) && isset($action['field'])) {
                        $params = [$action['field'] => $this->_getValue($row)];
                        if (isset($action['url']['params'])) {
                            $params[] = $action['url']['params'];
                        }

                        if(isset($action['url']['_iksCalled']))
                        {
                            if(is_array($action['url']['_iksCalled']))
                            {
                                foreach($action['url']['_iksCalled'] as $k => $v)
                                {
                                    $params[$k] = $v;
                                }
                            }
                        }
                        $action['href'] = $this->getUrl($action['url']['base'], $params);
                        unset($action['field']);
                    } else {
                        $action['href'] = $action['url'];
                    }
                    unset($action['url']);
                    break;

                case 'popup':
                    $action['onclick'] = 'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,'
                        . 'scrollbars=1\');return false;';
                    break;
            }
        }
        return $this;
    }

}
