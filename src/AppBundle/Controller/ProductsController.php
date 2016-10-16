<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Utils\CrumbsGenerator\InputData;
use AppBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\SearchProductsType;
use AppBundle\Form\OrderByType;

class ProductsController extends BaseController
{

    private $orderBy = 'title:ASC';

    /**
     * @Route(
     *     path="/products/{gender}/{category}/{page}",
     *     requirements = {"page" : "\d+",
     *                     "gender" : "\male|female"
     *                     }
     *     )
     */
    public function showByGenderAndCategoryAction(Request $request, $gender, $category, $page = 1)
    {
        $paginator = $this->get('app.product_paginator');
        $templateData['numPages'] = $paginator->countPagesByGenderAndCategory($gender, $category);
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $templateData['products'] = $this->get('app.product_paginator')->getPageByGenderAndCategory($gender, $category, $page, $this->orderBy);
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        dump($parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getAllParents($category));
        $crumbsData = [
            new InputData('app_home_home'),
            new InputData('app_products_showbygender', ['gender' => $gender]),];
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbygenderandcategory', ['gender' => $gender, 'category' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbygenderandcategory', ['gender'=>$gender, 'category'=>$category]);
        $templateData['sidebarCats'] = $this->getSidebarCats('app_products_showbygenderandcategory', ['gender'=>$gender]);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['form'] = $this->createCurrencyForm('app_products_showbygenderandcategory', ['gender' => $gender,'category' => $category, 'page' => $page])->createView();
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['page']= $page;
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeGenderCategoryPagesLinks($templateData['numPages'], $gender, $category);
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render("Products/products.html.twig", $templateData);
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
     * @Route(path="/products/search/{page}")
     * @param Request
     * @return Response
     */
    public function showSearchResultsAction(Request $request, $page = 1, $searchParams = null)
    {
        $paginator = $this->get('app.product_paginator');
        $query = $request->query->getAlnum('q');
        $className = $request->query->getAlnum('type');
        $templateData['numPages'] = $paginator->countSearchPages($query, $className);
        $templateData['page']= $page;
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $templateData['products'] = $this->get('app.product_paginator')->getSearchPage($query, $className, $page, $this->orderBy);
        $crumbsData = [new InputData('app_home_home'), new InputData('app_products_showsearchresults', null, 'Search')];
        $templateData['form'] =
            $this->createCurrencyForm('app_products_showsearchresults', ['q' => $query, 'type' => $className, 'page' => $page])->createView();
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $templateData['sidebarCats'] = $this->getSidebarCats('dummy', 'dummy');
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeSearchPagesLinks($templateData['numPages'], $query, $className);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render('Products/search.html.twig', $templateData);
    }

    /**
     * @param Request $request
     * @param $categoryName
     * @param int $page
     * @Route(path="/products/{categoryName}/{page}")
     * @return Response
     */
    public function showByCategoryAction(Request $request, $categoryName, $page = 1)
    {
        $paginator = $this->get('app.product_paginator');
        dump($templateData['numPages'] = $paginator->countPagesByCategory($categoryName));
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        dump($templateData['products'] = $this->get('app.product_paginator')->getPageByCategory($categoryName, $this->orderBy, $page));
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        dump($parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getAllParents($categoryName));
        $crumbsData[] = new InputData('app_home_home');
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbycategory', ['categoryName' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbycategory', ['categoryName' => $categoryName]);
        $templateData['sidebarCats'] = $this->getSidebarCats('app_products_showbycategory', ['categoryName'=>$categoryName]);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['form'] = $this->createCurrencyForm('app_products_showbycategory', ['category' => $categoryName, 'page' => $page])->createView();
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['page']= $page;
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeCategoryPagesLinks($templateData['numPages'], $categoryName);
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render("Products/products.html.twig", $templateData);
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
        if(isset($params['gender']))
            foreach($cats as $cat){
                $catData['count'] =  $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")
                    ->countProductsByGenderAndCategory($params['gender'], $cat->getName());
                $catData['link'] = $this->generateUrl($route, ['gender' => $params['gender'], 'category' => $cat->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
                $catData['name'] = $cat->getDisplayedName();
                $catsData []= $catData;
            }
        else
            foreach($cats as $cat){
                $catData['count'] =  $this->getDoctrine()->getManager()->getRepository("AppBundle:Product")
                    ->countProductsByCategory($cat->getName());
                $catData['link'] = $this->generateUrl('app_home_home');
                $catData['name'] = $cat->getDisplayedName();
                $catsData []= $catData;
            }
        return $catsData;
    }

    private function handleOrderByForm(Request $request)
    {
        $paginator = $this->get('app.product_paginator');
        $formData['orderBy'] = $paginator->getClientOrderBy();
        $form = $this->createForm(OrderByType::class, $formData);
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted()) {
            $this->orderBy = $form['orderBy']->getData();
            $paginator->setClientOrderBy($this->orderBy);
        }
        else{
            $orderBy = $paginator->getClientOrderBy();
            if($orderBy != null) $this->orderBy = $orderBy;
        }
        return $form->createView();
    }


}