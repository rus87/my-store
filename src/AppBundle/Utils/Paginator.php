<?php
namespace AppBundle\Utils;


use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use AppBundle\Utils\ProductManager;
use AppBundle\Entity\Category;

class Paginator
{
    private $em;
    private $requestStack;
    private $productManager;
    const PRODUCTS_PER_PAGE = 12;

    public function __construct(ProductManager $productManager, EntityManager $em, RequestStack $requestStack, Router $router)
    {
        $this->productManager = $productManager;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function countPagesByCategory($categoryName)
    {
        $productsCount = $this->productManager->countByCategory($categoryName);
        return (int)ceil($productsCount / Paginator::PRODUCTS_PER_PAGE);
    }

    public function countPagesByBrand($brandId)
    {
        $productsCount = $this->productManager->countByBrand($brandId);
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
        return $this->productManager->findByCategory($category->getName(), $orderBy, Paginator::PRODUCTS_PER_PAGE, $offset);
    }

    public function getPageByBrand($brandId, $orderBy, $page)
    {
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        return $this->productManager->findByBrand($brandId, $orderBy, Paginator::PRODUCTS_PER_PAGE, $offset);
    }

    public function countSearchPages($search, $class)
    {
        $productsCount = $this->productManager->countSearch($search, $class);
        return (int)ceil($productsCount / Paginator::PRODUCTS_PER_PAGE);
    }

    public function getSearchPage($search, $class, $page, $orderBy)
    {
        $offset = Paginator::PRODUCTS_PER_PAGE * ($page - 1);
        return $this->productManager->search($search, $class, $orderBy, Paginator::PRODUCTS_PER_PAGE, $offset);
    }

    public function makeSearchPagesLinks($numPages, $query, $type = null)
    {
        $links = [];
        $params = [];
        $params['q'] = $query;
        if($type) $params['type'] = $type;
        for($i=1; $i<=$numPages; $i++)
        {
            $params['page'] = $i;
            $links[] = $this->router
                ->generate('app_products_showsearchresults', $params);
        }
        return $links;
    }


    public function makeCategoryPagesLinks($numPages, $category)
    {
        $links = [];
        $params = $this->requestStack->getCurrentRequest()->query->all();
        for($i=1; $i<=$numPages; $i++)
        {
            $links[] = $this->router
                ->generate('app_products_showbycategory',
                    array_merge($params, ['page' => $i, 'categoryName' => $category]));
        }
        return $links;
    }

    public function makeByBrandPagesLinks($numPages, $brandId)
    {
        $links = [];
        $params = $this->requestStack->getCurrentRequest()->query->all();
        for($i=1; $i<=$numPages; $i++)
        {
            $links[] = $this->router
                ->generate('app_products_showbybrand',
                    array_merge($params, ['page' => $i, 'brandId' => $brandId]));
        }
        return $links;
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