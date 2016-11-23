<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ShippingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class)
            ->add('storageNum', IntegerType::class)
            ->add('city', TextType::class)
            ->add('storageAddress', TextType::class)
            ->add('clientTel', TextType::class)
            ->add('clientFio', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'AppBundle\Entity\Shipping'
            ]
        );
    }

    public function getName()
    {
        return 'shipping_type';
    }
}