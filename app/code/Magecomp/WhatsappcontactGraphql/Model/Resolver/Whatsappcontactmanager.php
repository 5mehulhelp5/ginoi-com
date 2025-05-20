<?php
namespace Magecomp\WhatsappcontactGraphql\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magecomp\Whatsappcontact\Model\WhatsappcontactManagement as apiModel;



class Whatsappcontactmanager implements ResolverInterface
{
    protected $whatsappmanager;
   
    public function __construct(
       apiModel $whatsappmanager
       
    ) {
        $this->whatsappmanager = $whatsappmanager;
      }

      /**
       * @param Field $field
       * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
       * @param ResolveInfo $info
       * @param array|null $value
       * @param array|null $args
       * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
       * @throws GraphQlInputException
       */

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try 
        {
            if (empty($args['storeid'])) {
                 $responseData=["message" => __("Please Pass Proper Store ID")];
                $data = ["status" => false, "response" => $responseData];
            }
            $responseData = $this->whatsappmanager->getConfigData($args['storeid']);
            $data = ["status" => true, "response" => $responseData];

            return $data;
            
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
