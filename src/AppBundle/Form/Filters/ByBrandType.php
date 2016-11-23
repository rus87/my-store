<?php
namespace AppBundle\Form\Filters;

use AppBundle\Form\FiltersType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ByBrandType extends FiltersType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('brand');
    }


    public function getName()
    {
        return 'byBrandFilters_type';
    }
}