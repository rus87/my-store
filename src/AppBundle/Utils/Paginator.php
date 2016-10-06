<?php
namespace AppBundle\Utils;


use Doctrine\ORM\EntityManager;

class Paginator
{
    private $em;
    const PRODUCTS_PER_PAGE = 9;

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

    public function getPageByGenderAndCategory($gender, $categoryName, $page, $orderBy)
    {
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        $category = $this->em->getRepository("AppBundle:Category")->findOneBy(['name' => $categoryName]);

        /*
        $products = $this->em->getRepository("AppBundle:Product")->findBy(
            ['gender' => $gender, 'category' => $category->getId()],
            [$orderBy => 'ASC'],
            Paginator::PRODUCTS_PER_PAGE,
            $offset);
        */

        $products = $this->em->getRepository("AppBundle:Product")->findProductsByGenderAndCategory(
            $gender, $categoryName, Paginator::PRODUCTS_PER_PAGE, $offset);

        return $products;
    }

    public function countPagesByGender($gender)
    {
        $productsCount =
            $this->em->getRepository("AppBundle:Product")->countProductsByGender($gender);
        $pagesCount = (int)ceil($productsCount / Paginator::PRODUCTS_PER_PAGE);
        return $pagesCount;
    }

    public function getPageByGender($gender, $page)
    {
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        $products = $this->em->getRepository("AppBundle:Product")
            ->getByGender($gender, null, Paginator::PRODUCTS_PER_PAGE, $offset);
        return $products;
    }
}