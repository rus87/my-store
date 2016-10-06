<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $imageConstraints = ['mimeTypes' => ['image/jpeg', 'image/jpg']];
        $image = new Image($imageConstraints);
            $builder
                ->add('name', FileType::class,
                [
                    'label' => $options['label'],
                    'required' => true,
                    'constraints' => $image,
                    'attr' => ['class' => 'form-control']
                ]);
                if($options['mode'] == 'add')
                    $builder->add('Del', ButtonType::class, ['attr' => ['class' => 'del_input btn btn-default']]);
                //elseif($options['mode'] == 'update')
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Photo',
            'mode' => 'add',
            'label' => false
        ));
    }

    public function getName()
    {
        return 'photo_type';
    }
}