<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 21.10.2016
 * Time: 8:42
 */

namespace AppBundle\Form\Filters;

use AppBundle\Form\Filters\SweaterType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class BlouseType extends SweaterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }


    public function getName()
    {
        return 'blouseFilters_type';
    }
}