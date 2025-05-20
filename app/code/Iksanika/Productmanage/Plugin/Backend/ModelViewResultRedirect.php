<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Plugin\Backend;

class ModelViewResultRedirect
{
    private $redirect;
    private $urlBuilder;
    private $_scopeConfig;
    private $_request;

    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config $config,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->redirect = $redirect;
        $this->urlBuilder = $urlBuilder;
        $this->_scopeConfig = $config;
        $this->_request = $request;
    }

    /**
     * Set url by path
     *
     * @param string $path
     * @param array $params
     * @return $this
     */
    public function aroundSetPath(\Magento\Backend\Model\View\Result\Redirect $subject, \Closure $proceed, $path, array $params = [])
    {
        if(
               ($path == 'catalog/*/' || $path == 'catalog/product/index')
            && ($this->_request->getParam('iksApmBack') != NULL)
        )
        {
            $path = 'productmanage/product/index';
        }

        if($this->_request->getParam('iksApmBack') != NULL)
        {
            if($path == 'catalog/*/new' || $path == 'catalog/*/edit')
            {
                $params['iksApmBack'] = '1';
            }
        }
//        echo $this->_request->getParam('iksApmBack');
//        var_dump($params);
//        echo '~~'.get_class($this->urlBuilder).'~~';
//die($path);
        $subject->setUrl($this->urlBuilder->getUrl($path, $this->redirect->updatePathParams($params)));
        return $subject;
    }

}
