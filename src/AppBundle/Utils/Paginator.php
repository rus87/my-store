<?php
namespace AppBundle\Utils;


use Doctrine\ORM\EntityManager;

class Paginator
{
    private $em;
    const PRODUCTS_PER_PAGE = 2;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function countPagesByGenderAndCategory($gender, $category)
    {
        $productsCount =
            $this->em->getRepository("AppBundle:Product")->countProductsByGenderAndCategory($gender, $category);
        $pagesCount = (int)ceil($productsCount / Paginator::PRODUCTS_PER_PAGE);
        return $pagesCount;
    }

    public function getPageProducts($gender, $category, $page)
    {
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        dump($offset);
        $products = $this->em->getRepository("AppBundle:Product")
            ->findProductsByGenderAndCategory($gender, $category, Paginator::PRODUCTS_PER_PAGE, $offset);
        return $products;
    }
}