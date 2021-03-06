<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Exclude;

/**
 * Shipping
 *
 * @ORM\Table(name="shipping")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ShippingRepository")
 */
class Shipping
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
     * @ORM\Column(name="title", type="string", length=50)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=50)
     */
    private $company;

    /**
     * @var int
     *
     * @ORM\Column(name="storageNum", type="integer")
     * @Assert\GreaterThan(0)
     */
    private $storageNum;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=50)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="storageAddress", type="string", length=50, nullable=true)
     */
    private $storageAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="clientTel", type="string", length=19)
     * @Assert\Regex(pattern="/\d+/")
     */
    private $clientTel;

    /**
     * @var string
     *
     * @ORM\Column(name="clientFio", type="string", length=50)
     */
    private $clientFio;

    /**
     * @ORM\OneToMany(targetEntity="Booking", mappedBy="shipping", cascade={}, fetch="EAGER")
     * @Exclude()
     */
    private $bookings;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="shippings")
     * @Exclude()
     */
    private $user;


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
     * Set company
     *
     * @param string $company
     *
     * @return Shipping
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set storageNum
     *
     * @param integer $storageNum
     *
     * @return Shipping
     */
    public function setStorageNum($storageNum)
    {
        $this->storageNum = $storageNum;

        return $this;
    }

    /**
     * Get storageNum
     *
     * @return int
     */
    public function getStorageNum()
    {
        return $this->storageNum;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Shipping
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set storageAddress
     *
     * @param string $storageAddress
     *
     * @return Shipping
     */
    public function setStorageAddress($storageAddress)
    {
        $this->storageAddress = $storageAddress;

        return $this;
    }

    /**
     * Get storageAddress
     *
     * @return string
     */
    public function getStorageAddress()
    {
        return $this->storageAddress;
    }

    /**
     * Set clientTel
     *
     * @param string $clientTel
     *
     * @return Shipping
     */
    public function setClientTel($clientTel)
    {
        $this->clientTel = $clientTel;

        return $this;
    }

    /**
     * Get clientTel
     *
     * @return string
     */
    public function getClientTel()
    {
        return $this->clientTel;
    }

    /**
     * Set clientFio
     *
     * @param string $clientFio
     *
     * @return Shipping
     */
    public function setClientFio($clientFio)
    {
        $this->clientFio = $clientFio;

        return $this;
    }

    /**
     * Get clientFio
     *
     * @return string
     */
    public function getClientFio()
    {
        return $this->clientFio;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bookings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add booking
     *
     * @param \AppBundle\Entity\Booking $booking
     *
     * @return Shipping
     */
    public function addBooking(\AppBundle\Entity\Booking $booking)
    {
        $this->bookings[] = $booking;
        $booking->setShipping($this);

        return $this;
    }

    /**
     * Remove booking
     *
     * @param \AppBundle\Entity\Booking $booking
     */
    public function removeBooking(\AppBundle\Entity\Booking $booking)
    {
        $this->bookings->removeElement($booking);
    }

    /**
     * Get bookings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Shipping
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
