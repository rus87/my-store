<?php
namespace AppBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Utils\CrumbsGenerator\InputData;
use AppBundle\Entity\Category;

class ProductsController extends BaseController
{
    public function indexAction()
    {

    }

    /**
     * @Route(
     *     path="/products/{gender}/{category}/{page}",
     *     requirements = {"page" : "\d+",
     *                     "gender" : "\male|female",
     *                     }
     *     )
     */
    public function showByGenderAndCategoryAction($gender, $category, $page = 1, $orderBy = 'id')
    {
        $numPages = $this->get('app.product_paginator')->countPagesByGenderAndCategory($gender, $category);
        $paginationLinkPath = $this
            ->generateUrl('app_products_showbygenderandcategory', ['gender' => $gender, 'category' => $category], UrlGeneratorInterface::ABSOLUTE_URL);
        $products = $this->get('app.product_paginator')->getPageByGenderAndCategory($gender, $category, $page, $orderBy);
        $categories = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        dump($parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getAllParents($category));
        $crumbsData = [
            new InputData('app_home_home'),
            new InputData('app_products_showbygender', ['gender' => $gender]),];
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbygenderandcategory',
                    ['gender' => $gender, 'category' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbygenderandcategory', ['gender'=>$gender, 'category'=>$category]);
        dump($crumbs = $this->get('app.crumbs_generator')->make($crumbsData));
        $sidebarCats = $this->getSidebarCats('app_products_showbygenderandcategory', ['gender'=>$gender]);
        $currency = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($products, $currency);
        $currencyForm = $this->createCurrencyForm('app_products_showbygenderandcategory', ['gender' => $gender,'category' => $category, 'page' => $page])->createView();
        return $this->render("Products/products.html.twig",
            [
                'crumbs' => $crumbs,
                'products' => $products,
                'numPages' => $numPages,
                'page' => $page,
                'paginationLinkPath' => $paginationLinkPath,
                'sidebarCats' => $sidebarCats,
                'categories' => $categories,
                'currency' => $currency,
                'form' => $currencyForm
            ]);
    }

    /**
     * @Route(
     *     path="/products/{gender}/{page}",
     *     requirements = {"page" : "\d+",
     *                     "gender" : "\male|female"}
     *     )
     */
    public function showByGenderAction($gender, $page = 1)
    {
        $products = $this->get('app.product_paginator')->getPageByGender($gender, $page);
        $categories = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $numPages = $this->get('app.product_paginator')->countPagesByGender($gender);
        $paginationLinkPath = $this
            ->generateUrl('app_products_showbygender', ['gender' => $gender], UrlGeneratorInterface::ABSOLUTE_URL);

        $crumbsData = [
            new InputData('app_home_home'),
            new InputData('app_products_showbygender', ['gender' => $gender])
        ];
        $crumbs = $this->get('app.crumbs_generator')->make($crumbsData);
        $sidebarCats = $this->getSidebarCats('app_products_showbygenderandcategory', ['gender'=>$gender]);
        $currency = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($products, $currency);
        $currencyForm = $this->createCurrencyForm('app_products_showbygender', ['gender' => $gender, 'page' => $page])->createView();

        return $this->render("Products/products.html.twig",
            [
                'crumbs' => $crumbs,
                'products' => $products,
                'numPages' => $numPages,
                'page' => $page,
                'paginationLinkPath' => $paginationLinkPath,
                'sidebarCats' => $sidebarCats,
                'categories' => $categories,
                'currency' => $currency,
                'form' => $currencyForm
            ]);
    }

    /**
     * @param $route
     * @param $params
     * @return array
     */
    private function getSidebarCats($route, $params)
    {
        $catsData = [];
        $cats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        foreach($cats as $cat){
            $catData['count'] =  $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")
                ->countProductsByGenderAndCategory($params['gender'], $cat->getName());
            $catData['link'] = $this->generateUrl($route, ['gender' => $params['gender'], 'category' => $cat->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
            $catData['name'] = $cat->getDisplayedName();
            $catsData []= $catData;
        }
        return $catsData;
    }

}