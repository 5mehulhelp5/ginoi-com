<?php
namespace Magecomp\Whatsapppro\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class CheckUserCreateObserver implements ObserverInterface
{
    protected $messageManager;
    protected $session;
    protected $_urlManager;
    protected $redirect;
    protected $_responseFactory;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {

        $this->messageManager = $messageManager;
        $this->session = $session;
        $this->_urlManager = $urlManager;
        $this->redirect = $redirect;
        $this->_responseFactory = $responseFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       
        try {
            $postdata = $observer->getRequest()->getPost();
            $finalnumber = $postdata['countryreg'].$postdata['mobilenumber'];
                $postdata['mobilenumber'] = $finalnumber;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
          
        return $this;
    }
}
