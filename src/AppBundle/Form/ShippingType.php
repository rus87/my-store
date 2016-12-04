<?php

namespace AppBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShippingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('company', TextType::class)
            ->add('storageNum', IntegerType::class)
            ->add('city', TextType::class)
            ->add('storageAddress', TextType::class)
            ->add('clientTel', TextType::class)
            ->add('clientFio', TextType::class)
            ->add('delete', SubmitType::class)
            ->add('save', SubmitType::class);
        if($options['user_id'] != null)
            $builder->add('shipping_select', EntityType::class,
                $this->getSelectOptions($options['em'], $options['user_id'], $options['select_ph']));
    }

    private function getSelectOptions($em, $user_id, $ph)
    {
        return [
            'class' => 'AppBundle:Shipping',
            'choice_label' => 'title',
            'query_builder' => $this->getQB($em, $user_id),
            'mapped' => false,
            'required' => false,
            'empty_data' => null,
            'placeholder' => $ph,
        ];

    }

    private function getQB(EntityManager $em, $uid)
    {
        $repo = $em->getRepository('AppBundle:Shipping');
            return $repo->createQueryBuilder('s')
                ->where('s.user = :uid')
                ->orderBy('s.title', 'ASC')
                ->setParameter('uid', $uid);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'AppBundle\Entity\Shipping',
                'user_id' => null,
                'em' => null,
                'select_ph' => 'Select shipping profile'
            ]
        );
    }

    public function getName()
    {
        return 'shipping_type';
    }
}