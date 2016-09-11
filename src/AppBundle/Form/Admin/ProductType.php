<?php
namespace AppBundle\Form\Admin;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Form\Admin\PhotoType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('price')
            ->add('description')
            ->add('gender')
            ->add('season')
            ->add('category', ChoiceType::class, ['mapped' => false, 'choices' => $options['categories']]);
        if($options['mode'] == 'add')
            $builder->add('photos', CollectionType::class, ['entry_type' => PhotoType::class]);
        elseif($options['mode'] == 'update'){
            $builder
                ->add('photos', CollectionType::class, ['entry_type' => RemovePhotoType::class, 'required' => false])
            ;
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Product',
            'categories' => '',
            'mode' => 'add'
        ));
    }

    public function getName()
    {
        return 'product_type';
    }
}