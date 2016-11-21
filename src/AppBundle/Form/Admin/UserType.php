<?php
namespace AppBundle\Form\Admin;

use AppBundle\Entity\Cart;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('role', EntityType::class,
            [
                'class' => 'AppBundle:Role',
                'choice_label' => 'title',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('r')
                        ->orderBy('r.id', 'DESC');
                }
            ])
        /*->add('cart', EntityType::class,
            [
                'class' => 'AppBundle:Cart',
                'choice_label' => 'id',
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('c')
                        //->where('c NOT IN (SELECT IDENTITY(u.cart) FROM AppBundle:User u)')
                        ->orderBy('c.id', 'ASC');
                },
                'multiple' => false,
            ])*/;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
        ));
    }
}