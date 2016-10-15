<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Regex;
class OrderByType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderBy', ChoiceType::class,
                [
                    'choices' => [
                        'Price ASC' => 'price:ASC',
                        'Price DESC' => 'price:DESC',
                        'Title DESC' => 'title:DESC',
                        'Title ASC' => 'title:ASC',],
                    'attr' => ['onchange' => 'send_form()'],
                    'constraints' => new Regex(['pattern' => '(price:ASC|price:DESC|title:ASC|title:DESC)'])
                ])
            ->add('params', HiddenType::class);
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
        return 'orderby_type';
    }
}