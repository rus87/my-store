<?php
namespace AppBundle\Form\Admin;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Form\Admin\PhotoType;
use AppBundle\Form\Admin\BrandType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('price')
            ->add('brand')
            ->add('description')
            ->add('discount')
            ->add('gender', ChoiceType::class, ['choices' => ['Male' => 'male', 'Female' => 'female']])
            ->add('brand', EntityType::class, ['class' => 'AppBundle:Brand', 'choice_label' => 'title'])
            ->add('category', ChoiceType::class, ['mapped' => false, 'choices' => $options['categories']]);
        if($options['mode'] == 'add')
            $builder
                ->add('mainPhoto1', PhotoType::class, ['mode' => 'no_del', 'label' => 'Main photo 1'])
                ->add('mainPhoto2', PhotoType::class, ['mode' => 'no_del', 'label' => 'Main photo 2'])
                ->add('photos', CollectionType::class,
                [
                    'entry_type' => PhotoType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                ]);
        elseif($options['mode'] == 'update'){
            $builder
                ->add('photos', CollectionType::class, ['entry_type' => RemovePhotoType::class, 'required' => false])
                ->add('newPhoto', PhotoType::class, ['required' => false, 'mode' => 'update', 'label' => 'Add photo'])
                ->add('mainPhoto1', PhotoType::class, ['mode' => 'no_del', 'mapped' => false, 'label' => 'Main photo 1', 'required' => false])
                ->add('mainPhoto2', PhotoType::class, ['mode' => 'no_del', 'mapped' => false, 'label' => 'Main photo 2', 'required' => false]);
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