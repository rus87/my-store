<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AppBundle\Form\ShippingType;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class)
            ->add('shipping', ShippingType::class,
                [
                    'em' => $options['em'],
                    'user_id' => $options['user_id']
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