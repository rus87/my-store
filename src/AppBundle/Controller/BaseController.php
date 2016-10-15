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

    protected $searchFormView;
    protected $searchRedirectResponse;

    /**
     * @param Product[] $products
     * @param Currency $currency
     * @return Product[]
     */
    protected function setProductsCurrency($products, $currency)
    {
        foreach($products as $product){
            $product->setCurrency($currency);
        }
        return $products;
    }

    /**
     * @Route(path="/handleCurrencyForm")
     */
    public function handleCurrencyFormAction(Request $request)
    {
        $params = [];
        $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:Currency");
        $currencies = $repo->findAll();
        foreach($currencies as $currency)
            $currenciesNames[$currency->getName()] = $currency->getName();
        $clientCurrency = new Currency();
        $form = $this->createForm(CurrencyType::class, $clientCurrency, ['currenciesNames' => $currenciesNames,]);
        $form->handleRequest($request);
        if($form->isValid()){
            $newClientCurrency = $repo->findOneBy(['name' => $clientCurrency->getName()]);
            $this->get('currency_manager')->setClientCurrency($newClientCurrency);
            $redirectRoute = $form['redirect_route']->getData();
            $params = json_decode($form['params']->getData(), true);
        }
        return $this->redirectToRoute($redirectRoute, $params);
    }

    /**
     * @param string $redirectRoute
     * @param array $params
     * @return \Symfony\Component\Form\Form
     */
    protected function createCurrencyForm($redirectRoute, $params)
    {
        $currenciesNames = [];
        $clientCurrency = $this->get('currency_manager')->getClientCurrency();
        $currencies = $this->getDoctrine()->getManager()->getRepository("AppBundle:Currency")->findAll();
        foreach($currencies as $currency)
            $currenciesNames[$currency->getName()] = $currency->getName();
        $form = $this->createForm(CurrencyType::class, $clientCurrency,
            [
                'currenciesNames' => $currenciesNames,
                'handler' => $this->generateUrl('app_base_handlecurrencyform'),
                'redirectRoute' => $redirectRoute,
                'params' => json_encode($params)
            ]);

        return $form;
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
        else $this->searchFormView = $form->createView();
    }

}