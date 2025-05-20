<?php

namespace Mageants\ExtensionVersionInformation\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageants\ExtensionVersionInformation\Model\Feed;

class PredispatchAdminActionControllerObserver implements ObserverInterface
{

    /**
     * @var Session
     */
    protected $_backendAuthSession;
    /**
     * @var Feed
     */
    protected $feed;

    public function __construct(
        Session $backendAuthSession,
        Feed $feed
    )
    {
        $this->_backendAuthSession = $backendAuthSession;
        $this->feed = $feed;
    }

    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $this->feed->checkUpdate();
        }
    }
}
