<?php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use AppBundle\Utils\CurrencyManager;
use AppBundle\Utils\Filters\FiltersHandler;

class ProductManager
{
    private $em;
    private $requestStack;
    private $currencyManager;
    private $filtersHandler;

    /**
     * ProductManager constructor.
     * @param $em
     * @param $requestStack
     * @param $currencyManager
     * @param $filtersHandler
     */
    public function __construct(RequestStack $requestStack, EntityManager $em, CurrencyManager $currencyManager, FiltersHandler $filtersHandler)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->currencyManager = $currencyManager;
        $this->filtersHandler = $filtersHandler;
    }

    public function findByCategory($catName, $orderBy = 'id:ASC', $limit = null, $offset = null)
    {
        $orderByDirection = explode(':', $orderBy)[1];
        $orderByProperty = explode(':', $orderBy)[0];
        $catRepo = $this->em->getRepository('AppBundle:Category');
        $className = $catRepo->getProductsClassName($catName);
        $fullClassName = 'AppBundle\Entity\Products\\'.ucfirst($className);
        $category = $catRepo->findOneBy(['name' => $catName]);
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from($fullClassName, 'p')
            ->where('p.category = :catId')
            ->setParameter('catId', $category->getId())
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy("p.$orderByProperty", $orderByDirection);
        $filters = $this->filtersHandler->getFilters($fullClassName);
        foreach($filters as $filter){
            //dump($filter);
            $qb->andWhere($filter->getQueryValue());
        }

        return $qb->getQuery()->getResult();
    }

    public function countByCategory($catName, $withFilters = true, $recursive = false)
    {
        $catRepo = $this->em->getRepository('AppBundle:Category');
        $className = $catRepo->getProductsClassName($catName);
        $fullClassName = 'AppBundle\Entity\Products\\'.ucfirst($className);
        $category = $catRepo->findOneBy(['name' => $catName]);
        $qb = $this->em->createQueryBuilder();
        $qb->select($qb->expr()->count('p.id'))
            ->from($fullClassName, 'p')
            ->where('p.category = :catId')
            ->setParameter('catId', $category->getId());
        if($withFilters){
            $filters = $this->filtersHandler->getFilters($fullClassName);
            foreach($filters as $filter)
                $qb->andWhere($filter->getQueryValue());
        }
        if($recursive){
            $childrenCats = $catRepo->getAllChildren($category);
            foreach($childrenCats as $cat){
                $name = $cat->getName();
                $qb->orWhere("p.category = :$name");
                $qb->setParameter($name, $cat->getId());
            }
        }
        return $qb->getQuery()->getSingleScalarResult();
    }



}
