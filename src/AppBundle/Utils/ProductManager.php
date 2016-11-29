<?php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use AppBundle\Utils\CurrencyManager;
use AppBundle\Utils\PhotoManager;
use AppBundle\Utils\Filters\FiltersHandler;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Entity\Photo;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductManager
{
    private $em;
    private $requestStack;
    private $currencyManager;
    private $filtersHandler;
    private $photoManager;

    /**
     * ProductManager constructor.
     * @param RequestStack $requestStack
     * @param EntityManager $em
     * @param CurrencyManager $currencyManager
     * @param FiltersHandler $filtersHandler
     * @param PhotoManager $photoManager
     */
    public function __construct(RequestStack $requestStack, EntityManager $em, CurrencyManager $currencyManager, FiltersHandler $filtersHandler, PhotoManager $photoManager)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->currencyManager = $currencyManager;
        $this->filtersHandler = $filtersHandler;
        $this->photoManager = $photoManager;
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

    public function setMainPhoto($productId, File $file, $photoNum = 1)
    {
        if($photoNum == 2){
            $getMainPhoto = 'getMainPhoto2';
            $setMainPhoto = 'setMainPhoto2';
        }
        else{
            $setMainPhoto = 'setMainPhoto1';
            $getMainPhoto = 'getMainPhoto1';
        }
        $product = $this->em->getRepository('AppBundle:Product')->find($productId);
        $photo = new Photo();
        $photo->setName(md5(uniqid()).'.'.$file->guessExtension());
        if( ($oldPhoto = $product->$getMainPhoto()) != null )
            $this->photoManager->remove($oldPhoto->getId());
        $product->$setMainPhoto($photo);
        $file->move($product->getPhotosDirectory(), $photo->getName());
        $this->photoManager->downscale($product->getPhotosDirectory().'/'.$photo->getName());
        $this->em->flush();
    }

    public function addPhoto($productId, File $file)
    {
        $product = $this->em->getRepository('AppBundle:Product')->find($productId);
        if(! $product) throw new NotFoundHttpException('Product with id='.$productId.' not found.');
        $photo = new Photo();
        $photo->setProduct($product);
        $photo->setName(md5(uniqid()).'.'.$file->guessExtension());
        $product->addPhoto($photo);
        $file->move($product->getPhotosDirectory(), $photo->getName());
        $this->photoManager->downscale($product->getPhotosDirectory().'/'.$photo->getName());
        $this->em->flush();
    }
}
