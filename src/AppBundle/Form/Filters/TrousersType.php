<?php

namespace AppBundle\Form\Filters;

use AppBundle\Form\FiltersType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TrousersType extends FiltersType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('waistMin', IntegerType::class, ['required' => false])
            ->add('waistMax', IntegerType::class, ['required' => false])
            ->add('lengthMin', IntegerType::class, ['required' => false])
            ->add('lengthMax', IntegerType::class, ['required' => false]);
    }


    public function getName()
    {
        return 'trousersFilters_type';
    }
}