<?php
namespace AppBundle\Controller;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use AppBundle\Form\FiltersType;
use AppBundle\Form\Filters\JacketType;
use AppBundle\Form\Filters\SweaterType;
use AppBundle\Form\Filters\BlouseType;

class ProductsController extends BaseController
{

    private $orderBy = 'title:ASC';
    private $filtersRedirectResponse = null;
    const PRICE_SLIDER_VALUES = ['min' => 0, 'max' => 80, 'rangeMin' => 0, 'rangeMax' => 80];


    /**
     * @Route(path="/products/search/{page}")
     * @param Request
     * @return Response
     */
    public function showSearchResultsAction(Request $request, $page = 1, $searchParams = null)
    {
        $filtrator = $this->get('product_filtrator');
        $paginator = $this->get('app.product_paginator');
        $query = $request->query->getAlnum('q');
        $className = $request->query->getAlnum('type');
        $templateData['numPages'] = $paginator->countSearchPages($query, $className);
        $templateData['page']= $page;
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $products = $this->get('app.product_paginator')->getSearchPage($query, $className, $page, $this->orderBy);
        $templateData['products'] = $this->get('product_filtrator')->applyFilters($products, $filtrator->getFilters());
        $crumbsData = [new InputData('app_home_home'), new InputData('app_products_showsearchresults', null, 'Search')];
        $templateData['form'] =
            $this->createCurrencyForm('app_products_showsearchresults', ['q' => $query, 'type' => $className, 'page' => $page])->createView();
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $templateData['sidebarCats'] = $this->getSidebarCats('app_products_showbycategory');
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeSearchPagesLinks($templateData['numPages'], $query, $className);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['filtersForm'] = $this->handleFiltersForm($request, 'app_products_showsearchresults', ['q' => $query, 'type' => $className]);
        if($this->filtersRedirectResponse) return $this->filtersRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        return $this->render('Products/search.html.twig', $templateData);
    }

    /**
     * @Route(
     *      Path="/products/price-slider-init",
     *      options={"expose" : "true"}
     *      )
     * @return JsonResponse
     */
    public function priceSliderInitAction()
    {
        $sliderValues = null;
        $currency = $this->get('currency_manager')->getClientCurrency();
        foreach(ProductsController::PRICE_SLIDER_VALUES as $key => $value)
            $sliderValues[$key] = $value * $currency->getRatio();
        return new JsonResponse(json_encode($sliderValues));
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
        $filtersHandler = $this->get('filters_handler');
        $paginator = $this->get('app.product_paginator');
        $templateData['numPages'] = $paginator->countPagesByCategory($categoryName);
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $templateData['childrenCats'] = $this->getChildrenCats($categoryName, 'app_products_showbycategory', ['categoryName'=>$categoryName]);
        $templateData['products'] = $this->get('app.product_paginator')->getPageByCategory($categoryName, $this->orderBy, $page);
        $parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getAllParents($categoryName, true);
        $crumbsData[] = new InputData('app_home_home');
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbycategory', ['categoryName' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbycategory', ['categoryName' => $categoryName]);
        $templateData['sidebarCats'] = $this->getSidebarCats('app_products_showbycategory', ['categoryName'=>$categoryName]);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['page']= $page;
        $templateData['filtersTpl'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getProductsClassName($categoryName);
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeCategoryPagesLinks($templateData['numPages'], $categoryName);
        if(($tmp = $filtersHandler->handleForm('app_products_showbycategory', ['categoryName' => $categoryName], $categoryName)) instanceof RedirectResponse) return $tmp;
        else $templateData['filtersForm'] = $tmp;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_products_showbycategory', ['categoryName' => $categoryName, 'page' => $page]);
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        return $this->render("Products/products.html.twig", $templateData);
    }


    /**
     * @param $route
     * @param $params
     * @return array
     */
    private function getSidebarCats($route, $params = null)
    {
        $catsData = [];
        $cats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        if($route == 'app_products_showbycategory')
            foreach($cats as $cat){
                $catData['count'] = $this->get('product_manager')->countByCategory($cat->getName(), false, true);
                $catData['link'] = $this->generateUrl($route, ['categoryName' => $cat->getName()]);
                $catData['name'] = $cat->getDisplayedName();
                $catsData []= $catData;
            }
        return $catsData;
    }

    private function getChildrenCats($categoryName, $route, $params)
    {
        $catsData = [];
        $category = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findOneBy(['name' => $categoryName]);
        $children = $category->getChildren();
        if($route == 'app_products_showbycategory')
            foreach($children as $cat){
                $catData['count'] = $this->get('product_manager')->countByCategory($cat->getName(), false, true);
                $catData['link'] = $this->generateUrl($route, ['categoryName' => $cat->getName()]);
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