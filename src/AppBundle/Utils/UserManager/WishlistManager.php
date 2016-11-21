<?php
namespace AppBundle\Utils\UserManager;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\Wishlist;
use Doctrine\ORM\EntityManager;

class WishlistManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var User
     */
    private $user;

    public function __construct(EntityManager $em, User $user)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * @param Product $product
     * @return User|null
     */
    public function addProduct(Product $product)
    {
        $wishlist = $this->user->getWishlist();
        if(! $wishlist){
            $wishlist = $this->createWishlist();
        }
        if(! $wishlist->getProducts()->contains($product)){
            $wishlist->addProduct($product);
            $this->em->flush();
        }
        return $this->user;
    }

    /**
     * @param Product $product
     * @return User|null
     */
    public function removeProduct(Product $product)
    {
        $wishlist = $this->user->getWishlist();
        if($wishlist){
            $wishlist->removeProduct($product);
            if($wishlist->getProducts()->isEmpty()){
                $this->user->setWishlist(null);
                $this->em->remove($wishlist);
            }
            $this->em->flush();
        }
        return $this->user;
    }

    /**
     * @param Product $product
     * @return User
     */
    public function toggleProduct(Product $product)
    {
        $wishlist = $this->user->getWishlist();
        if($wishlist == null)
            $wishlist = $this->createWishlist();
        $wishlist->getProducts()->contains($product) ? $this->removeProduct($product) :
            $this->addProduct($product);
        return $this->user;
    }

    /**
     * @return Wishlist
     */
    private function createWishlist()
    {
        $wishlist = new Wishlist();
        $this->user->setWishlist($wishlist);
        $this->em->flush();
        return $this->user->getWishlist();
    }
}