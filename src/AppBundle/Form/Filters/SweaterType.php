<?php

namespace AppBundle\Form\Filters;

use AppBundle\Form\FiltersType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SweaterType extends FiltersType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('sleeveMin', IntegerType::class, ['required' => false])
            ->add('sleeveMax', IntegerType::class, ['required' => false]);
    }


    public function getName()
    {
        return 'sweaterFilters_type';
    }
}