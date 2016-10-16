<?php
namespace AppBundle\Utils;


use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use AppBundle\Entity\Category;

class Paginator
{
    private $em;
    private $requestStack;
    const PRODUCTS_PER_PAGE = 9;

    public function __construct(EntityManager $em, RequestStack $requestStack, Router $router)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function countPagesByCategory($categoryName)
    {
        $productsCount = $this->em->getRepository("AppBundle:Product")->countProductsByCategory($categoryName);
        dump($productsCount);
        return (int)ceil($productsCount / Paginator::PRODUCTS_PER_PAGE);
    }

    public function getPageByCategory($category, $orderBy, $page)
    {
        $catsRepo = $this->em->getRepository('AppBundle:Category');
        $prodsRepo = $this->em->getRepository('AppBundle:Product');
        $orderByProperty = explode(':', $orderBy)[0];
        $orderByDirection = explode(':', $orderBy)[1];
        if(! $category instanceof Category)
            if(gettype($category) == "string")
                $category = $catsRepo->findOneBy(['name' => $category]);
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        return $prodsRepo->findBy(
            ['category' => $category->getId()],
            [$orderByProperty => $orderByDirection],
            Paginator::PRODUCTS_PER_PAGE,
            $offset);
    }

    public function countSearchPages($search, $productClassName)
    {
        $productsCount =
            $this->em->getRepository("AppBundle:Product")->countSearch($search, $productClassName);
        return (int)ceil($productsCount / Paginator::PRODUCTS_PER_PAGE);
    }

    public function getSearchPage($search, $productClassName, $page, $orderBy)
    {
        $repo = $this->em->getRepository('AppBundle:Product');
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        return $repo->search($search, $productClassName, $orderBy, Paginator::PRODUCTS_PER_PAGE, $offset);
    }

    public function makeSearchPagesLinks($numPages, $query, $type)
    {
        $links = [];
        for($i=1; $i<=$numPages; $i++)
        {
            $links[] = $this->router
                ->generate('app_products_showsearchresults', ['page' => $i, 'q' => $query, 'type' => $type]);
        }
        return $links;
    }

    public function makeGenderCategoryPagesLinks($numPages, $gender, $category)
    {
        $links = [];
        for($i=1; $i<=$numPages; $i++)
        {
            $links[] = $this->router
                ->generate('app_products_showbygenderandcategory', ['page' => $i, 'gender' => $gender, 'category' => $category]);
        }
        return $links;
    }

    public function makeCategoryPagesLinks($numPages, $category)
    {
        $links = [];
        for($i=1; $i<=$numPages; $i++)
        {
            $links[] = $this->router
                ->generate('app_products_showbycategory', ['page' => $i, 'categoryName' => $category]);
        }
        return $links;
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
            $gender, $categoryName, $orderBy, Paginator::PRODUCTS_PER_PAGE, $offset);

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

    /**
     * @param string $orderBy
     */
    public function setClientOrderBy($orderBy)
    {
        $cookie = new Cookie('ob', $this->encodeOrderBy($orderBy), new \DateTime('12-12-2020'));
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->send();
    }

    /**
     * @return null|string
     */
    public function getClientOrderBy()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->cookies->has('ob') ? $value = $request->cookies->get('ob') : $value = null;
        return $this->decodeOrderBy($value);
    }

    /**
     * @param string $value
     * @return null|string
     */
    private function decodeOrderBy($value)
    {
        $orderBy = null;
        switch ($value){
            case 1:
                $orderBy = 'price:ASC';
                break;
            case 2:
                $orderBy = 'price:DESC';
                break;
            case 3:
                $orderBy = 'title:ASC';
                break;
            case 4:
                $orderBy = 'title:DESC';
                break;
        }
        return $orderBy;
    }

    /**
     * @param string $orderBy
     * @return int|null
     */
    private function encodeOrderBy($orderBy)
    {
        $value = null;
        switch ($orderBy){
            case 'price:ASC':
                $value = 1;
                break;
            case 'price:DESC':
                $value = 2;
                break;
            case 'title:ASC':
                $value = 3;
                break;
            case 'title:DESC':
                $value = 4;
                break;
        }
        return $value;
    }

}