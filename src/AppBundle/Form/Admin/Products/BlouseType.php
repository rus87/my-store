<?php

namespace AppBundle\Form\Admin\Products;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlouseType extends SweaterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Products\Blouse',
            'categories' => '',
            'mode' => 'add'
        ]);
    }

    public function getName()
    {
        return 'blouse_type';
    }

}