<?php
namespace Magecomp\WhatsappproGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magecomp\Whatsapppro\Api\WhatsappproInterface;

class CreditmemoNotifier implements ResolverInterface
{
  protected $WhatsappproInterface;

    public function __construct(
       WhatsappproInterface $WhatsappproInterface
    ) {

         $this->WhatsappproInterface=$WhatsappproInterface;
    }


    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            if (empty($args['creditmemoId'])) {
                throw new GraphQlInputException(
                    __(
                        'Please, Enter Creditmemo Id.'
                    )
                );
            }
          
            $responce=$this->WhatsappproInterface->sendCreditmemoNotification($args['creditmemoId']);
            return json_decode($responce);
          
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__("Requested Creditmemo is not found."));
        } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }
}
