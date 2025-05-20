<?php

namespace Mageants\Orderattachment\Model\Config;

use Magento\Config\Model\Config;
use Magento\Framework\App\RequestInterface;
use Mageants\Orderattachment\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class ExtensionValidation
{
    /**
     * @var string
     */
    public $valid_message = '';
    
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Mageants\Orderattachment\Helper\Data
     */
    public $helperData;

    /**
     * ConfigPlugin constructor.
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
    }

    /**
     * Plugin method to intercept the save process before saving configuration.
     *
     * @param Config $subject
     * @param callable $proceed
     * @return Config
     * @throws LocalizedException
     */
    public function aroundSave(Config $subject, callable $proceed)
    {
        $valueToCheck = $this->request->getParams();
        if (is_array($valueToCheck) && isset($valueToCheck['groups']['general']['fields']['extension']['value'])) {
            $extension = strtolower($valueToCheck['groups']['general']['fields']['extension']['value']);
            if (!$this->isValid($extension)) {
                throw new LocalizedException(__($this->valid_message));
            }
        }
        $result = $proceed();
        return $result;
    }

    /**
     * Custom validation logic. Replace this with your own validation rules.
     *
     * @param string $value
     * @return bool
     */
    private function isValid($value)
    {
        $protected_extensions = $this->helperData->getProtectedExtensions();
        $value = explode(',', $value);
        $dis_aalowed = [];
        if (is_array($value) && !empty($value)) {
            foreach ($value as $val) {
                if (isset($protected_extensions[$val])) {
                    $dis_aalowed[] = $val;
                }
            }
        }
        if (!empty($dis_aalowed)) {
            $count = count($dis_aalowed);
            if ($count == 1) {
                $dis_aalowed_ = implode(', ', $dis_aalowed);
                $this->valid_message = $dis_aalowed_ . ' file type not allowed.';
            } else {
                $dis_aalowed[$count-2] = $dis_aalowed[$count-2] . ' and ' . $dis_aalowed[$count-1];
                unset($dis_aalowed[$count-1]);
                $dis_aalowed_ = implode(', ', $dis_aalowed);
                $this->valid_message = $dis_aalowed_ . ' file types not allowed.';
            }
            return false;
        }
        return true;
    }
}
