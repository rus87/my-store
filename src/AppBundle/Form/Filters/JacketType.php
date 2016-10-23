<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 20.10.2016
 * Time: 13:53
 */

namespace AppBundle\Form\Filters;

use AppBundle\Form\FiltersType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;



class JacketType extends FiltersType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('sleeveMin', IntegerType::class, ['required' => false])
            ->add('sleeveMax', IntegerType::class, ['required' => false]);
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
        return 'jacketFilters_type';
    }
}