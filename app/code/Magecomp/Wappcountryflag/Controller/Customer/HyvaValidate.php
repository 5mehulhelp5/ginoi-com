<?php

namespace Magecomp\Wappcountryflag\Controller\Customer;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class HyvaValidate extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $helperdata;
    protected $_jsonResultFactory;

    public function __construct(Context $context,
                                \Magecomp\Wappcountryflag\Helper\Data $helperdata,
                                \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
                                \Magento\Email\Model\Template\Filter $filter)
    {
        $this->helperdata = $helperdata;
        $this->_jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

     public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $jsonData = json_decode(file_get_contents('php://input'), true);

        $mobilenumber = $jsonData['mobile'];
        $countrycode = $jsonData['countrycode'];
        $country_id = $jsonData['country_id'];
        $moblenght = $jsonData['mobilelength'];
        $count = strlen($countrycode);
        $jsonResult = $this->_jsonResultFactory->create();
        $validate=$this->helperdata->getCountryvalidation($country_id);
        if(($validate+$count) != $moblenght && $validate!=false){
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $jsonResult->setData(['digit' => $validate,'validate' => false,'message'=>__("Your Mobile Number Must be ".$validate." digit long with country code.")]);
            return $jsonResult;
        } else{
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $jsonResult->setData(['digit' => $validate, 'validate' => true,'message'=>"",'mobilenumber'=>$mobilenumber]);
            return $jsonResult;
        }     
    }
}
