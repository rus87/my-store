<?php

namespace AppBundle\Utils\UserManager;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\Wishlist;
use AppBundle\Utils\UserManager\WishlistManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @Secure(roles="ROLE_USER")
 */
class UserManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    private $wishlistManager;



    public function __construct(EntityManager $em, AuthorizationChecker $authorizationChecker, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param $product
     * @return User|null
     */
    public function addProductToWishlist(Product $product)
    {
        $this->wishlistManager = new WishlistManager($this->em, $this->getCurrentUser());
        return $this->wishlistManager->addProduct($product);
    }

    /**
     * @param $product
     * @return User|null
     */
    public function removeProductFromWishlist(Product $product)
    {
        $this->wishlistManager = new WishlistManager($this->em, $this->getCurrentUser());
        return $this->wishlistManager->removeProduct($product);
    }

    /**
     * @param $product
     * @return User
     */
    public function toggleProductInWishlist(Product $product)
    {
        $this->wishlistManager = new WishlistManager($this->em, $this->getCurrentUser());
        return $this->wishlistManager->toggleProduct($product);
    }


    /**
     * @return User|null
     */
    public function getCurrentUser()
    {
        $user= null;
        if($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            $user = $this->tokenStorage->getToken()->getUser();
        return $user;
    }

}