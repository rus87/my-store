<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 17.10.2016
 * Time: 13:28
 */

namespace AppBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class FiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('priceMin', NumberType::class, ['required' => false])
            ->add('priceMax', NumberType::class, ['required' => false])
            ->add('brand', EntityType::class,
                [
                    'class' => 'AppBundle:Brand',
                    'choice_label' => 'title',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => $this->getQb($options['em'], $options['cat']),
                    'data' => $this->getInitChecks($options['em'], $options['cat'], $options['checked'])
                ])
            ->add('gender', ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => ['Men' => 'male', 'Women' => 'female', 'Both' => 'both'],
                    'required' => false,
                ]);
    }

    protected function getQb(EntityManager $em, $cat)
    {
        $brandRepo = $em->getRepository('AppBundle:Brand');
        return $brandRepo->createQueryBuilder('b')
            ->innerJoin('b.categories', 'c', 'WITH', 'c.name = :cat')
            ->setParameter('cat', ucfirst($cat))
            ->orderBy('b.title', 'ASC');
    }

    protected function getInitChecks(EntityManager $em, $cat, $checkedValues = null)
    {
        $brands = $this->getQb($em, $cat)->getQuery()->getResult();
        if($checkedValues == 'all')
            return $brands;
        elseif ($checkedValues == null)
            return null;
        else{
            foreach($brands as $key => $brand)
                if(! in_array($brand->getId(), $checkedValues))
                    unset($brands[$key]);
            return $brands;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'em' => '',
                'cat' => '',
                'checked' => []
            ]
        );
    }

    public function getName()
    {
        return 'filters_type';
    }
}