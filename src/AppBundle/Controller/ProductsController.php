<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Utils\CrumbsGenerator\InputData;

class ProductsController extends Controller
{
    public function indexAction()
    {

    }

    /**
     * @Route(
     *     path="/products/{gender}/{category}/{page}",
     *     requirements = {"page" : "\d+",
     *                     "gender" : "\male|female",
     *                     "category" : "[a-z]{3,10}"}
     *     )
     */
    public function productsShowAction($gender, $category, $page = 1)
    {

        $products = $this->getDoctrine()->getRepository("AppBundle:Product")
            ->findProductsByGenderAndCategory($gender, $category);
        $crumbsData = [
            new InputData('app_home_home'),
            new InputData('app_products_productsbygender', ['gender' => $gender]),
            new InputData('app_products_productsshow', ['gender'=>$gender, 'category'=>$category])
        ];
        dump($crumbs = $this->get('app.crumbs_generator')->make($crumbsData));
        dump($products);
        //dump($this->get("app.product_paginator")->getPageProducts($gender, $category, $page));

        return $this->render("Products/products.html.twig", ['crumbs' => $crumbs]);
    }

    /**
     * @Route(
     *     path="/products/{gender}/{page}",
     *     requirements = {"page" : "\d+",
     *                     "gender" : "\male|female"}
     *     )
     */
    public function productsByGenderAction($gender, $page = 1)
    {
        $products = $this->getDoctrine()->getRepository("AppBundle:Product")->findBy(['gender' => $gender]);
        dump($products);
        $crumbsData = [
            new InputData('app_home_home'),
            new InputData('app_products_productsbygender', ['gender' => $gender])
        ];
        dump($crumbs = $this->get('app.crumbs_generator')->make($crumbsData));


        return $this->render("Products/products.html.twig", ['crumbs' => $crumbs]);
    }

}