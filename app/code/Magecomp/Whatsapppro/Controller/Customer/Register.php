<?php
namespace Magecomp\Whatsapppro\Controller\Customer;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Register extends \Magento\Framework\App\Action\Action
{
    protected $helperapi;
    protected $helpercustomer;
    protected $helperdata;
    protected $emailfilter;
    protected $_jsonResultFactory;

    public function __construct(
        Context $context,
        \Magecomp\Whatsapppro\Helper\Apicall $helperapi,
        \Magecomp\Whatsapppro\Helper\Customer $helpercustomer,
        \Magecomp\Whatsapppro\Helper\Data $helperdata,
        JsonFactory $jsonResultFactory,
        \Magento\Email\Model\Template\Filter $filter
    ) {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
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
            $moblenght = $this->getRequest()->getParam('mobilelength');

            $validate=$this->helperdata->getCountryvalidation($countrycode);

            if ($validate != $moblenght && $validate!=false) {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $jsonResult->setData(['validate' => false,'message'=>__("Your Mobile No Must be ".$validate." Digit Long.")]);
                return $jsonResult;
            } else {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $jsonResult->setData(['validate' => true,'message'=>""]);
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
