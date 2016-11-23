<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentRepository")
 */
class Comment
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
     * @ORM\Column(name="content", type="string", length=500)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="placed", type="datetimetz")
     */
    private $placed;

    /**
     * @ORM\ManyToOne(targetEntity="Wishlist", inversedBy="comments")
     * @Assert\NotBlank()
     * @ORM\JoinColumn(nullable=false)
     */
    private $wishlist;

    /**
     * @ORM\OneToOne(targetEntity="Product")
     * @Assert\NotBlank()
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;


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
     * Set content
     *
     * @param string $content
     *
     * @return Comment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set placed
     *
     * @param \DateTime $placed
     *
     * @return Comment
     */
    public function setPlaced($placed)
    {
        $this->placed = $placed;

        return $this;
    }

    /**
     * Get placed
     *
     * @return \DateTime
     */
    public function getPlaced()
    {
        return $this->placed;
    }

    /**
     * Set wishlist
     *
     * @param \AppBundle\Entity\Wishlist $wishlist
     *
     * @return Comment
     */
    public function setWishlist(\AppBundle\Entity\Wishlist $wishlist)
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

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     *
     * @return Comment
     */
    public function setProduct(\AppBundle\Entity\Product $product)
    {
        $this->product = $product;
        $product->setComment($this);

        return $this;
    }

    /**
     * Get product
     *
     * @return \AppBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
