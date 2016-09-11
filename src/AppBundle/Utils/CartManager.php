<?php
namespace AppBundle\Utils;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Product;
use AppBundle\Entity\Cart;

class CartManager
{
    private $em;
    private $requestStack;

    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function putProduct()
    {

    }

    public function getProducts()
    {
        $clientCartHash = $this->getClientCartHash();
        $cart = $this->em->getRepository("AppBundle:Cart")->findOneBy(["hash" => $clientCartHash]);
        is_object($cart) ? $products = $cart->getProducts() : $products = NULL;
        return $products;
    }

    public function pullProduct(Product $product)
    {

        $clientCartHash = $this->getClientCartHash();
        if($clientCartHash)
        {
            $cart = $this->em->getRepository("AppBundle:Cart")->findOneBy(["hash" => $clientCartHash]);
            $cart->addProduct($product);
            $this->em->persist($cart);
            $this->em->flush();
        }
        else{
            $newCart = $this->createCart();
            $newCart->addProduct($product);
            $this->em->flush();
        }

    }

    private function createCart()
    {
        $cart = new Cart();
        $cart->setHash(md5(uniqid()));
        $cookie = new Cookie("cartHash", $cart->getHash());
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->send();
        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }

    private function getClientCartHash()
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request->cookies->get("cartHash");
    }

}