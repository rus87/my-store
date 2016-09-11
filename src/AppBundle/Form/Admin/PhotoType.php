<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $imageConstraints = ['mimeTypes' => ['image/jpeg', 'image/jpg']];
        $image = new Image($imageConstraints);
            $builder->add('name', FileType::class, ['label' => 'Photo', 'required' => true, 'constraints' => $image]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Photo',
            'mode' => 'add'
        ));
    }

    public function getName()
    {
        return 'photo_type';
    }
}