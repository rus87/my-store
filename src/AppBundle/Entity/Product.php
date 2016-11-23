<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\AccessType;



/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "jacket" = "AppBundle\Entity\Products\Jacket",
 *     "sweater" = "AppBundle\Entity\Products\Sweater",
 *     "trousers" = "AppBundle\Entity\Products\Trousers",
 *     "blouse" = "AppBundle\Entity\Products\Blouse",})
 *
 */
abstract class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     * @AccessType("public_method")
     */
    protected $price;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Brand", inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id")
     * @Assert\Valid()
     * @MaxDepth(1)
     */
    protected $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=10)
     * @Assert\Regex(pattern="(male|female)")
     */
    protected $gender;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Assert\Valid()
     * @MaxDepth(1)
     */
    protected $category;


    /**
     * @ORM\ManyToMany(targetEntity="Cart", mappedBy="products")
     * @MaxDepth(2)
     */
    protected $carts;


    /**
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="product", cascade={"persist", "remove"}, fetch="EAGER")
     * @Assert\Valid
     */
    protected $photos;


    protected $newPhoto;


    /**
     * @var string
     * Needed for serialization
     */
    protected $miniCartPhotoPath;

    /**
     * @ORM\OneToOne(targetEntity="Photo", cascade={"persist", "remove"}, fetch="EAGER")
     */
    protected $mainPhoto1;

    /**
     * @ORM\OneToOne(targetEntity="Photo", cascade={"persist", "remove"}, fetch="EAGER")
     */
    protected $mainPhoto2;

    /**
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="products")
     */
    protected $booking;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @ORM\Column(name="discount", type="integer", nullable=true, options={"default": null})
     * @Assert\Range(min = 0, max = 100)
     */
    protected $discount;

    /**
     * @ORM\ManyToMany(targetEntity="Wishlist", mappedBy="products")
     */
    protected $wishlists;

    /**
     * @ORM\OneToOne(targetEntity="Comment")
     */
    protected $comment;

    /**
     * @var string
     * Needed for serialization
     */
    public $wishlistThumbPath;

    /**
     * @var float
     * Needed for serialization
     */
    public $priceDisc;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getNewPhoto()
    {
        return $this->newPhoto;
    }

    /**
     * @param mixed $newPhoto
     */
    public function setNewPhoto($newPhoto)
    {
        $this->newPhoto = $newPhoto;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @param bool $withDiscount
     * @return float
     */
    public function getPrice($withDiscount = false)
    {
        isset($this->currency) ? $price = round($this->price * $this->currency->getRatio(), 2) :
            $price = $this->price;
        if(($this->discount != null) && $withDiscount) $price -= round(($price / 100) * $this->discount, 2);
        return $price;
    }


    /**
     * Set description
     *
     * @param string $description
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set brand
     *
     * @param \AppBundle\Entity\Brand $brand
     *
     * @return Product
     */
    public function setBrand(\AppBundle\Entity\Brand $brand = null)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * Get brand
     *
     * @return \AppBundle\Entity\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }


    /**
     * Set gender
     *
     * @param string $gender
     * @return Product
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return Product
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add carts
     *
     * @param \AppBundle\Entity\Cart $carts
     * @return Product
     */
    public function addCart(\AppBundle\Entity\Cart $carts)
    {
        $this->carts[] = $carts;

        return $this;
    }

    /**
     * Remove carts
     *
     * @param \AppBundle\Entity\Cart $carts
     */
    public function removeCart(\AppBundle\Entity\Cart $carts)
    {
        $this->carts->removeElement($carts);
    }

    /**
     * Get carts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCarts()
    {
        return $this->carts;
    }
    


    /**
     * Add photo
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return Product
     */
    public function addPhoto(\AppBundle\Entity\Photo $photo)
    {
        $this->photos[] = $photo;
        $photo->setProduct($this);

        return $this;
    }

    /**
     * Remove photo
     *
     * @param \AppBundle\Entity\Photo $photo
     */
    public function removePhoto(\AppBundle\Entity\Photo $photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }




    /**
     * Make all product's photos to reference to this product
     */
    public function updatePhotosReferences()
    {
        foreach($this->getPhotos() as $photo){
            $photo->setProduct($this);
        }
    }

    /**
     * Set mainPhoto1
     *
     * @param \AppBundle\Entity\Photo $mainPhoto1
     *
     * @return Product
     */
    public function setMainPhoto1(\AppBundle\Entity\Photo $mainPhoto1 = null)
    {
        $this->mainPhoto1 = $mainPhoto1;

        return $this;
    }

    /**
     * Get mainPhoto1
     *
     * @return \AppBundle\Entity\Photo
     */
    public function getMainPhoto1()
    {
        return $this->mainPhoto1;
    }

    /**
     * @return string
     */
    public function getMainPhoto1Path()
    {
        $photo = $this->getMainPhoto1();
        if($photo != null)
            return 'img/Products/'.$this->getId().'/'.$photo->getName();
        else return 'img/dummy-product-1.jpg';
    }

    /**
     * Set mainPhoto2
     *
     * @param \AppBundle\Entity\Photo $mainPhoto2
     *
     * @return Product
     */
    public function setMainPhoto2(\AppBundle\Entity\Photo $mainPhoto2 = null)
    {
        $this->mainPhoto2 = $mainPhoto2;

        return $this;
    }

    /**
     * Get mainPhoto2
     *
     * @return \AppBundle\Entity\Photo
     */
    public function getMainPhoto2()
    {
        return $this->mainPhoto2;
    }

    /**
     * @return string
     */
    public function getMainPhoto2Path()
    {
        $photo = $this->getMainPhoto2();
        if($photo != null)
            return 'img/Products/'.$this->getId().'/'.$photo->getName();
        else return 'img/dummy-product-2.jpg';
    }

    /**
     * @return string
     */
    public function getMiniCartPhotoPath()
    {
        return $this->miniCartPhotoPath;
    }

    /**
     * @param string $miniCartPhotoPath
     */
    public function setMiniCartPhotoPath($miniCartPhotoPath)
    {
        $this->miniCartPhotoPath = $miniCartPhotoPath;
    }

    /**
     * Set booking
     *
     * @param \AppBundle\Entity\Booking $booking
     *
     * @return Product
     */
    public function setBooking(\AppBundle\Entity\Booking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \AppBundle\Entity\Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @return \AppBundle\Entity\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \AppBundle\Entity\Currency $currency
     * @return Product
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function isReserved()
    {
        if($this->booking == null) return false;
        return true;
    }

    public function getPhotosDirectory()
    {
        return "img/Products/".$this->getId();
    }

    public static function getAvailableFilters()
    {
        return ['priceMin', 'priceMax', 'gender', 'brand'];
    }




    /**
     * Set discount
     *
     * @param integer $discount
     *
     * @return Product
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return integer
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Add wishlist
     *
     * @param \AppBundle\Entity\Wishlist $wishlist
     *
     * @return Product
     */
    public function addWishlist(\AppBundle\Entity\Wishlist $wishlist)
    {
        $this->wishlists[] = $wishlist;

        return $this;
    }

    /**
     * Remove wishlist
     *
     * @param \AppBundle\Entity\Wishlist $wishlist
     */
    public function removeWishlist(\AppBundle\Entity\Wishlist $wishlist)
    {
        $this->wishlists->removeElement($wishlist);
    }

    /**
     * Get wishlists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWishlists()
    {
        return $this->wishlists;
    }

    /**
     * Set comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Product
     */
    public function setComment(\AppBundle\Entity\Comment $comment = null)
    {
        $this->comment = $comment;
        $comment->setProduct($this);

        return $this;
    }

    /**
     * Get comment
     *
     * @return \AppBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}
