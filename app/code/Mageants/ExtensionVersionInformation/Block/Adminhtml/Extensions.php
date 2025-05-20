<?php

namespace Mageants\ExtensionVersionInformation\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\ModuleResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\Manager;

class Extensions extends Field
{
    protected $_template = 'Mageants_ExtensionVersionInformation::modules.phtml';

    /**
     * @var FullModuleList
     */
    protected $fullModuleList;
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;
    /**
     * @var StoreManagerInterface
     */
    protected $storeInterface;
    /**
     * @var ModuleResource
     */
    protected $moduleResource;
    /**
     * @var Manager
     */
    protected $_moduleManager;
    public function __construct(
        Context $context,
        FullModuleList $fullModuleList,
        Curl $curl,
        ModuleListInterface $moduleList,
        ModuleResource $moduleResource,
        StoreManagerInterface $storeInterface,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->curl = $curl;
        $this->moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
        $this->storeInterface = $storeInterface;
        $this->_moduleManager = $moduleManager;

        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $columns = $this->getRequest()->getParam('website') || $this->getRequest()->getParam('store') ? 5 : 4;
        return $this->_decorateRowHtml($element, "<td colspan='{$columns}'>" . $this->toHtml() . '</td>');
    }

    public function getModulesList()
    {
        $username = 'admin';
        $password = 'admin@324';        
        $this->curl->setCredentials($username,$password);

        $this->curl->get('https://www.mageants.com/extensions.json');
        $data = $this->curl->getBody();
        $data = json_decode($data, true);

        $extension = [];
        $modules = $this->fullModuleList->getAll();
        sort($modules);

        foreach ($modules as $module) {
            if (strpos($module['name'], 'Mageants') !== false) {
                if (array_key_exists($module['name'], $data)) {
                    $ext_name = str_replace('_', ' ', $module['name']);

                    if ($this->_moduleManager->isEnabled($module['name']) == 1) {
                        $extension[] = [
                            'name' => $ext_name,
                            'current_version' => $module['setup_version'],
                            'data' => $data[$module['name']],
                            'topfive' => $data['topExtension'],
                            'supportContain' => $data['supportContainer'],
                            'extensionServiceContainer' => $data['extensionServiceContainer'],
                            'supportContainerHeader' => $data['supportContainerHeader'],
                            'topContainerHeader' => $data['topContainerHeader'],
                        ];
                    }
                }
            }
        }
        return $extension;
    }
}
