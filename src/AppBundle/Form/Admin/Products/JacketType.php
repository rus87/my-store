<?php

namespace AppBundle\Form\Admin\Products;

use AppBundle\Form\Admin\ProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class JacketType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('sleeveLength', NumberType::class)
            ->add('filling', TextType::class)
            ->add('outerMaterial', TextType::class)
            ->add('seasonality', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Products\Jacket',
            'categories' => '',
            'mode' => 'add'
        ]);
    }

    public function getName()
    {
        return 'jacket_type';
    }

}