<?php

namespace AppBundle\Utils;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Currency;
use AppBundle\Entity\Product;

class CurrencyManager
{
    private $em;
    private $requestStack;
    const DEFAULT_CURRENCY = 'USD';

    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    /**
     * @return null|Currency
     */
    public function getClientCurrency()
    {
        $request = $this->requestStack->getCurrentRequest();
        $currency = $this->em->getRepository('AppBundle:Currency')->findOneBy(['name' => $request->cookies->get("currency")]);
        if(!$currency)
            $currency = $this->em->getRepository('AppBundle:Currency')->findOneBy(['name' => CurrencyManager::DEFAULT_CURRENCY]);
        return $currency;
    }

    /**
     * @param string|Currency $input
     * @return null|Currency
     */
    public function setClientCurrency($input)
    {
        if($input instanceof Currency)
            $currency = $input;
        else
            $currency = $this->em->getRepository('AppBundle:Currency')->findOneBy(['name' => $input]);

        $cookie = new Cookie('currency', $currency->getName(), new \DateTime("01-01-2020"));
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->send();

        return $currency;
    }

    /**
     * @param Product[] $products
     * @return Product[]
     */
    public function setProductsCurrency($products)
    {
        foreach($products as $product){
            $product->setCurrency($this->getClientCurrency());
        }
        return $products;
    }
}