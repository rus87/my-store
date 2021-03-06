<?php
namespace AppBundle\Utils;


use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Product;
use AppBundle\Entity\Cart;
use Symfony\Component\Security\Core\User\User;
use AppBundle\Utils\UserManager\UserManager;


class CartManager
{
    private $em;
    private $um;
    private $requestStack;

    public function __construct(EntityManager $em, RequestStack $requestStack, UserManager $um)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->um = $um;
    }

    /**
     * @param Product $product
     * @return Cart
     */
    public function removeProduct(Product $product)
    {
        $cart = $this->getCart();
        $cart->removeProduct($product);
        $this->em->flush();
        return $cart;
    }

    /**
     * @param Product $product
     * @return Cart
     */
    public function pullProduct(Product $product)
    {
        $cart = $this->getCart();
        if(! $product->isReserved())
        {
            $cart->addProduct($product);
            $this->em->persist($cart);
            $this->em->flush();
        }
        return $cart;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCartProducts()
    {
        return $this->getCart()->getProducts();
    }

    /**
     * @return Cart
     */
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
        if(($user = $this->um->getCurrentUser()) != null){
            $cart = $user->getCart();
        }
        else{
            $clientCartHash = $this->getClientCartHash();
            $cart = $this->em->getRepository("AppBundle:Cart")->findOneBy(["hash" => $clientCartHash]);
            if($cart == null){
                $cart = $this->createCart();
                $this->em->persist($cart);
                $this->em->flush();
            }
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

    /**
     * @param Product $product
     * @return Cart
     */
    public function toggleProduct(Product $product)
    {
        $cart = $this->getCart();
        if($cart->getProducts()->contains($product))
            $this->removeProduct($product);
        else
            $this->pullProduct($product);
        return $cart;
    }

}