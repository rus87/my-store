<?php
/**
 * Created by PhpStorm.
 * User: volkhonovich.ri
 * Date: 27.10.2016
 * Time: 15:15
 */

namespace AppBundle\Form\Filters;

use AppBundle\Form\FiltersType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SearchType extends FiltersType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    protected function getQb(EntityManager $em, $cat = null)
    {
        $brandRepo = $em->getRepository('AppBundle:Brand');
        return $brandRepo->createQueryBuilder('b')
            ->select()
            ->orderBy('b.title', 'ASC');
    }

}