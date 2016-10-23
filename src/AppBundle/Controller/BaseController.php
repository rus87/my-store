<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Currency;
use AppBundle\Form\CurrencyType;
use AppBundle\Form\SearchProductsType;

class BaseController extends Controller
{

    protected $searchRedirectResponse;
    protected $currencyRedirectResponse;

    /**
     * @param Product[] $products
     * @param Currency $currency
     * @return Product[]
     */
    protected function setProductsCurrency($products = null, $currency)
    {
        if($products)
            foreach($products as $product){
                $product->setCurrency($currency);
            }
        return $products;
    }

    protected function handleCurrencyForm(Request $request, $redirectRoute, $params)
    {
        $currenciesNames = [];
        $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:Currency");
        $clientCurrency = $this->get('currency_manager')->getClientCurrency();
        $currencies = $repo->findAll();
        foreach($currencies as $currency)
            $currenciesNames[$currency->getName()] = $currency->getName();
        $form = $this->createForm(CurrencyType::class, $clientCurrency, ['currenciesNames' => $currenciesNames]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $newClientCurrency = $repo->findOneBy(['name' => $clientCurrency->getName()]);
            $this->get('currency_manager')->setClientCurrency($newClientCurrency);
            if(($priceMin = $request->query->get('priceMin')) != null){
                $priceMin /= $clientCurrency->getRatio();
                $priceMin *= $newClientCurrency->getRatio();
                $request->query->set('priceMin', round($priceMin, 2));
            }
            if(($priceMax = $request->query->get('priceMax')) != null){
                $priceMax /= $clientCurrency->getRatio();
                $priceMax *= $newClientCurrency->getRatio();
                $request->query->set('priceMax', round($priceMax, 2));
            }
            $params = array_merge($params, $request->query->all());
            $this->currencyRedirectResponse = $this->redirectToRoute($redirectRoute, $params);
        }
        return $form->createView();
    }

    /**
     * @return mixed
     * @param Request
     *
     */
    protected function handleSearchForm(Request $request, $formData = null)
    {
        $searchCats = $this->getDoctrine()->getManager()->getRepository('AppBundle:Category')->findBy(['parent' => null]);
        foreach($searchCats as $cat)
            $choices[$cat->getDisplayedName()] = $cat->getName();
        $form = $this->createForm(SearchProductsType::class, $formData, ['choices' => $choices]);
        $form->handleRequest($request);
        if($form->isValid()){
            $search = $form['query']->getData();
            $className = $form['className']->getData();
            $this->searchRedirectResponse =
                $this->redirectToRoute('app_products_showsearchresults', ['q' => $search, 'type' => $className]);
        }
        else return $form->createView();
    }


}