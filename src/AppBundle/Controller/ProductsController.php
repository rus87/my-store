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
use JMS\Serializer\SerializationContext;

class ProductsController extends BaseController
{

    private $orderBy = 'title:ASC';
    private $filtersRedirectResponse = null;
    const PRICE_SLIDER_VALUES = ['min' => 0, 'max' => 80, 'rangeMin' => 0, 'rangeMax' => 80];


    /**
     * @Route(path="/products/search/{page}")
     * @param Request $request
     * @return Response
     */
    public function showSearchResultsAction(Request $request, $page = 1, $searchParams = null)
    {
        $filtersHandler = $this->get('filters_handler');
        $paginator = $this->get('app.product_paginator');
        $query = $request->query->getAlnum('q');
        $className = $request->query->getAlnum('type');
        $templateData['numPages'] = $paginator->countSearchPages($query, $className);
        $templateData['productsCount'] = $this->get('product_manager')->countSearch($query, $className);
        $templateData['page']= $page;
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $templateData['products'] = $paginator->getSearchPage($query, $className, $page, $this->orderBy);
        $crumbsData = [new InputData('app_home_home'), new InputData('app_products_showsearchresults', null, 'Search')];
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['categories'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $templateData['sidebarCats'] = $this->getSidebarCats();
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeSearchPagesLinks($templateData['numPages'], $query, $className);
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $templateData['brands'] = $this->getSidebarBrands();
        $templateData['filtersTpl'] = 'Search';
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        if(($tmp = $filtersHandler->handleForm('app_products_showsearchresults', ['q' => $query, 'type' => $className], 'search')) instanceof RedirectResponse) return $tmp;
        else $templateData['filtersForm'] = $tmp;
        $templateData['searchForm'] = $this->handleSearchForm($request, ['className' => $className, 'query' => $query]);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_products_showsearchresults', ['q' => $query, 'type' => $className, 'page' => $page]);
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
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
     * @Route(path="/products/by-category/{categoryName}/{page}")
     * @return Response
     */
    public function showByCategoryAction(Request $request, $categoryName, $page = 1)
    {
        $filtersHandler = $this->get('filters_handler');
        $paginator = $this->get('app.product_paginator');
        $templateData['numPages'] = $paginator->countPagesByCategory($categoryName);
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $templateData['productsCount'] = $this->get('product_manager')->countByCategory($categoryName);
        $templateData['childrenCats'] = $this->getChildrenCats($categoryName, 'app_products_showbycategory');
        $templateData['products'] = $this->get('app.product_paginator')->getPageByCategory($categoryName, $this->orderBy, $page);
        $parentCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getAllParents($categoryName, true);
        $crumbsData[] = new InputData('app_home_home');
        if($parentCats){
            foreach($parentCats as $parentCat)
                $crumbsData []= new InputData('app_products_showbycategory', ['categoryName' => $parentCat->getName()]);
        }
        $crumbsData[] = new InputData('app_products_showbycategory', ['categoryName' => $categoryName]);
        $templateData['sidebarCats'] = $this->getSidebarCats();
        $this->setProductsCurrency($templateData['products'], $this->get('currency_manager')->getClientCurrency());
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['page'] = $page;
        $templateData['filtersTpl'] = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->getProductsClassName($categoryName);
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeCategoryPagesLinks($templateData['numPages'], $categoryName);
        $templateData['brands'] = $this->getSidebarBrands();
        if(($tmp = $filtersHandler->handleForm('app_products_showbycategory', ['categoryName' => $categoryName], $categoryName)) instanceof RedirectResponse) return $tmp;
        else $templateData['filtersForm'] = $tmp;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_products_showbycategory', ['categoryName' => $categoryName, 'page' => $page]);
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        return $this->render("Products/products.html.twig", $templateData);
    }

    /**
     * @param Request $request
     * @param $brand
     * @param int $page
     * @Route(path="/products/by-brand/{brandId}/{page}")
     * @return Response
     */
    public function showByBrandAction(Request $request, $brandId, $page = 1)
    {
        $this->getDoctrine()->getManager()->getRepository('AppBundle:Category')->getAllAsCatalog();
        $filtersHandler = $this->get('filters_handler');
        $paginator = $this->get('app.product_paginator');
        $templateData['productsCount'] = $this->get('product_manager')->countByBrand($brandId);
        $templateData['numPages'] = $paginator->countPagesByBrand($brandId);
        $templateData['orderByForm'] = $this->handleOrderByForm($request);
        $templateData['childrenCats'] = null;
        $templateData['products'] = $this->get('app.product_paginator')->getPageByBrand($brandId, $this->orderBy, $page);
        $crumbsData[] = new InputData('app_home_home');
        $crumbsData[] = new InputData('app_products_showbybrand', ['brandId' => $brandId]);
        $templateData['sidebarCats'] = $this->getSidebarCats();
        $templateData['currency'] = $this->get('currency_manager')->getClientCurrency();
        $this->setProductsCurrency($templateData['products'], $templateData['currency']);
        $templateData['crumbs'] = $this->get('app.crumbs_generator')->make($crumbsData);
        $templateData['page']= $page;
        $templateData['filtersTpl'] = 'ByBrand';
        $templateData['brands'] = $this->getSidebarBrands();
        $templateData['pagesLinks'] = $this->get('app.product_paginator')->makeByBrandPagesLinks($templateData['numPages'], $brandId);
        if(($tmp = $filtersHandler->handleForm('app_products_showbybrand', ['brandId' => $brandId], 'by_brand')) instanceof RedirectResponse) return $tmp;
        else $templateData['filtersForm'] = $tmp;
        $templateData['searchForm'] = $this->handleSearchForm($request);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_products_showbybrand', ['brandId' => $brandId, 'page' => $page]);
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        return $this->render("Products/products.html.twig", $templateData);
    }

    /**
     * @Route(path="/products/getcatstree", options={"expose" : "true"})
     */
    public function getCatsTreeAction()
    {
        $rootCats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
        $catsStr = $this->get('jms_serializer')
            ->serialize($rootCats, 'json');
        dump($catsStr);
        return new JsonResponse($catsStr);
    }

    /**
     * @return array
     */
    private function getSidebarCats()
    {
        $catsData = [];
        $cats = $this->getDoctrine()->getManager()->getRepository("AppBundle:Category")->findBy(['parent' => null]);
            foreach($cats as $cat){
                $catData['count'] = $this->get('product_manager')->countByCategory($cat->getName(), false, true);
                $catData['link'] = $this->generateUrl('app_products_showbycategory', ['categoryName' => $cat->getName()]);
                $catData['name'] = $cat->getDisplayedName();
                $catsData []= $catData;
            }
        return $catsData;
    }

    private function getSidebarBrands()
    {
        $brands = $this->getDoctrine()->getManager()->getRepository('AppBundle:Brand')->findAll();
        $out = [];
        foreach($brands as $brand){
            $brandData['title'] = $brand->getTitle();
            $brandData['count'] = $this->get('product_manager')->countByBrand($brand->getId(), false);
            $brandData['link'] = $this->generateUrl('app_products_showbybrand', ['brandId' => $brand->getId()]);
            $out[] = $brandData;
        }
        return $out;
    }

    private function getChildrenCats($categoryName, $route)
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