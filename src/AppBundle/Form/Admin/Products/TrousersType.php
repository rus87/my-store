<?php

namespace AppBundle\Form\Admin\Products;

use AppBundle\Form\Admin\ProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TrousersType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('waist', IntegerType::class)
            ->add('length', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Products\Trousers',
            'categories' => '',
            'mode' => 'add'
        ]);
    }

    public function getName()
    {
        return 'trousers_type';
    }

}