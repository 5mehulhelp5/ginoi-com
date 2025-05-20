<?php
namespace Magecomp\Wappcountryflag\Controller\Customer;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Validatephone extends \Magento\Framework\App\Action\Action
{
    protected $helperdata;
    protected $emailfilter;
    protected $_jsonResultFactory;

    public function __construct(
        Context $context,
        \Magecomp\Wappcountryflag\Helper\Data $helperdata,
        JsonFactory $jsonResultFactory,
        \Magento\Email\Model\Template\Filter $filter
    ) {
        $this->helperdata = $helperdata;
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->emailfilter = $filter;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $jsonResult = $this->_jsonResultFactory->create();
            $mobilenumber = $this->getRequest()->getParam('mobile');
            $countrycode = $this->getRequest()->getParam('countrycode');
            $country_id= $this->getRequest()->getParam('country_id');
            $count = strlen($country_id);
            $moblenght = $this->getRequest()->getParam('mobilelength');
            $validate=$this->helperdata->getCountryvalidation($countrycode);

            if ($validate != $moblenght && ($validate+$count) != $moblenght && $validate!=false) {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $jsonResult->setData(['validate' => false,'message'=>__("Your Mobile No Must be ".$validate." Digit Long.")]);
                return $jsonResult;
            } else {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $jsonResult->setData(['validate' => true,'message'=>"",'mobilenumber'=>$mobilenumber]);
                return $jsonResult;
            }
        } catch (\Exception $e) {
            $data = ["error"];
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            return $resultJson;
        }
    }
}
