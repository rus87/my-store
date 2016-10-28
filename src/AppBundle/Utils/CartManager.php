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

    public function removeProduct($productId)
    {
        $product = $this->em->getRepository("AppBundle:Product")->findOneById($productId);
        if (!$product)
            throw $this->createNotFoundException('Нет продукта с идом '.$productId);
        $clientCartHash = $this->getClientCartHash();
        $cart = $this->em->getRepository("AppBundle:Cart")->findOneBy(["hash" => $clientCartHash]);
        $cart->removeProduct($product);
        $this->em->flush();
    }

    public function getCartProducts()
    {
        return $this->getCart()->getProducts();
    }

    public function pullProduct(Product $product)
    {
        if(! $product->isReserved())
        {
            $cart = $this->getCart();
            $cart->addProduct($product);
            $this->em->persist($cart);
            $this->em->flush();
        }
    }

    private function createCart()
    {
        $cart = new Cart();
        $cart->setHash(md5(uniqid()));
        $cookie = new Cookie("cartHash", $cart->getHash(), new \DateTime("01-01-2020"));
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

    /**
     * @return Cart
     */
    public function getCart()
    {
        $clientCartHash = $this->getClientCartHash();
        $cart = $this->em->getRepository("AppBundle:Cart")->findOneBy(["hash" => $clientCartHash]);
        if($cart == null){
            $cart = $this->createCart();
            $this->em->persist($cart);
            $this->em->flush();
        }
        return $cart;
    }

    /**
     * @return Cart
     */
    public function clearCart()
    {
        $cart = $this->getCart();
        foreach($cart->getProducts() as $cartProduct)
            $cart->removeProduct($cartProduct);
        $this->em->flush();
        return $cart;
    }
}