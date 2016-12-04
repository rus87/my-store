<?php

namespace AppBundle\Form;

use AppBundle\Utils\UserManager\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AppBundle\Form\ShippingType;

class BookingType extends AbstractType
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var string
     */
    private $email;

    /**
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;

        if(($user = $this->userManager->getCurrentUser()) != null)
            $this->email = $user->getEmail();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('email', TextType::class, ['data' => $this->email])
            ->add('shipping', ShippingType::class,
                [
                    'em' => $options['em'],
                    'user_id' => $options['user_id'],
                    'allow_extra_fields' => true
                ]);
        $builder->setAction('javascript:void(null);');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'AppBundle\Entity\Booking',
                'em' => null,
                'user_id' => null
            ]
        );
    }

    public function getName()
    {
        return 'booking_type';
    }
}