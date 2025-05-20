<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Plugin\Backend;

class Url
{

    private $_request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
    }

    public function beforeGetUrl(\Magento\Backend\Model\Url $subject, $routePath = null, $routeParams = null)
    {

        if($this->_request->getParam('iksApmBack') != NULL)
        {
            if(    $routePath == 'catalog/*/new'
                || $routePath == 'catalog/product/new'
                || $routePath == 'catalog/*/save'
                || $routePath == 'catalog/product/save'
                || $routePath == 'catalog/*/edit'
                || $routePath == 'catalog/product/edit'
                || $routePath == 'catalog/*/validate'
                || $routePath == 'catalog/product/validate'
            )
            {
                $routeParams['iksApmBack'] = '1';
            }
        }

        return [$routePath, $routeParams];
    }

}
