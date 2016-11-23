<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="users", fetch="EAGER")
     * @Assert\NotBlank()
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @var string
     * @Assert\Length(min=6, max=100)
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity="Shipping", mappedBy="user")
     */
    private $shippings;

    /**
     * @ORM\OneToOne(targetEntity="Cart", inversedBy="user")
     */
    private $cart;

    /**
     * @ORM\OneToOne(targetEntity="Wishlist", inversedBy="user", cascade={"persist"})
     */
    private $wishlist;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->shippings = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * Add shipping
     *
     * @param \AppBundle\Entity\Shipping $shipping
     *
     * @return User
     */
    public function addShipping(\AppBundle\Entity\Shipping $shipping)
    {
        $this->shippings[] = $shipping;

        return $this;
    }

    /**
     * Remove shipping
     *
     * @param \AppBundle\Entity\Shipping $shipping
     */
    public function removeShipping(\AppBundle\Entity\Shipping $shipping)
    {
        $this->shippings->removeElement($shipping);
    }

    /**
     * Get shippings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShippings()
    {
        return $this->shippings;
    }

    /**
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return User
     */
    public function setCart(\AppBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \AppBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }


    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return [$this->getRole()->getTitle()];
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string Username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return $this
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
        return $this;
    }



    /**
     * Set role
     *
     * @param \AppBundle\Entity\Role $role
     *
     * @return User
     */
    public function setRole(\AppBundle\Entity\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \AppBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set wishlist
     *
     * @param \AppBundle\Entity\Wishlist $wishlist
     *
     * @return User
     */
    public function setWishlist(\AppBundle\Entity\Wishlist $wishlist = null)
    {
        $this->wishlist = $wishlist;

        return $this;
    }

    /**
     * Get wishlist
     *
     * @return \AppBundle\Entity\Wishlist
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }
}
