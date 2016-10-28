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

    public function findByBrand($brand, $orderBy = 'id:ASC', $limit = null, $offset = null)
    {
        $orderByDirection = explode(':', $orderBy)[1];
        $orderByProperty = explode(':', $orderBy)[0];
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from('AppBundle\Entity\Product', 'p')
            ->where('p.brand = :brand')
            ->setParameter('brand', $brand)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy("p.$orderByProperty", $orderByDirection);
        $filters = $this->filtersHandler->createFiltersFromQuery(FiltersHandler::FILTER_SETS['by_brand']);
        foreach($filters as $filter){
            $qb->andWhere($filter->getQueryValue());
        }
        return $qb->getQuery()->getResult();
    }

    public function countByBrand($brandId, $withFilters = true)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($qb->expr()->count('p.id'))
            ->from('AppBundle:Product', 'p')
            ->where('p.brand = :brandId')
            ->setParameter('brandId', $brandId);
        if($withFilters){
            $filters = $this->filtersHandler->createFiltersFromQuery(FiltersHandler::FILTER_SETS['by_brand']);
            foreach($filters as $filter)
                $qb->andWhere($filter->getQueryValue());
        }
        return $qb->getQuery()->getSingleScalarResult();
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
        $filters = $this->filtersHandler->createFiltersFromQuery($fullClassName::getAvailableFilters());
        foreach($filters as $filter){
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
            $filters = $this->filtersHandler->createFiltersFromQuery($fullClassName::getAvailableFilters());
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

    public function search($search, $class = null, $orderBy = 'id:ASC', $limit = null, $offset = null)
    {
        $class == null ? $class = 'AppBundle\Entity\Product' : $class = 'AppBundle\Entity\Products\\'.ucfirst($class);
        $orderByDirection = explode(':', $orderBy)[1];
        $orderByProperty = explode(':', $orderBy)[0];
        $qb = $this->em->createQueryBuilder();
        $search = "%".$search."%";
        $qb->select('p')
            ->from($class, 'p')
            ->where('p.title LIKE :search')
            ->orWhere('p.description LIKE :search')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy("p.$orderByProperty", $orderByDirection)
            ->setParameter('search', $search);
        $filters = $this->filtersHandler->createFiltersFromQuery(FiltersHandler::FILTER_SETS['search']);
        foreach($filters as $filter){
            $qb->andWhere($filter->getQueryValue());
        }
        return $qb->getQuery()->getResult();
    }

    public function countSearch($search, $class = null)
    {
        $class == null ? $class = 'AppBundle\Entity\Product' : $class = 'AppBundle\Entity\Products\\'.ucfirst($class);
        $qb = $this->em->createQueryBuilder();
        $search = "%".$search."%";
        $qb->select($qb->expr()->count('p.id'))
            ->from($class, 'p')
            ->where('p.title LIKE :search')
            ->orWhere('p.description LIKE :search')
            ->setParameter('search', $search);
        $filters = $this->filtersHandler->createFiltersFromQuery(FiltersHandler::FILTER_SETS['search']);
        foreach($filters as $filter){
            $qb->andWhere($filter->getQueryValue());
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

}
