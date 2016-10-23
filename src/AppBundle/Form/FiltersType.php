<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 17.10.2016
 * Time: 13:28
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class FiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('priceMin', NumberType::class, ['required' => false])
            ->add('priceMax', NumberType::class, ['required' => false])
            ->add('gender', ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => ['Men' => 'male', 'Women' => 'female', 'Both' => 'both'],
                    'required' => false,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [

            ]
        );
    }

    public function getName()
    {
        return 'filters_type';
    }
}