<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 09.09.2016
 * Time: 13:38
 */

namespace AppBundle\Form\Admin;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RemovePhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delete', CheckboxType::class, ['label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Photo',
        ));
    }

    public function getName()
    {
        return 'remove_photo_type';
    }
}