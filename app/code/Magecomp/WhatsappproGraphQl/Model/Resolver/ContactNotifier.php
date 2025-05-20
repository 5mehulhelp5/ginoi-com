<?php
namespace Magecomp\WhatsappproGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magecomp\Whatsapppro\Api\WhatsappproInterface;

class ContactNotifier implements ResolverInterface
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
            if (empty($args['mobilenumber'])) {
                throw new GraphQlInputException(
                    __(
                        'Please, Enter Mobile Number.'
                    )
                );
            }
            if (empty($args['email'])) {
                throw new GraphQlInputException(
                    __(
                        'Please, Enter Your Email.'
                    )
                );
            }
            if (empty($args['name'])) {
                throw new GraphQlInputException(
                    __(
                        'Please, Enter Your Name.'
                    )
                );
            }
            if (empty($args['comment'])) {
                throw new GraphQlInputException(
                    __(
                        'Please, Enter Your Query.'
                    )
                );
            }

             $responce=$this->WhatsappproInterface->sendContactNotification($args['name'],$args['email'],$args['mobilenumber'],$args['comment'],$args['storeId']);
            return json_decode($responce);
           


        } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }
}
