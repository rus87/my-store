<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Currency;
use AppBundle\Form\CurrencyType;


class BaseController extends Controller
{

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

}