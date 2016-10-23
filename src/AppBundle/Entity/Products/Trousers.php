<?php

namespace AppBundle\Entity\Products;

use AppBundle\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trousers
 *
 * @ORM\Table(name="products\trousers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Products\TrousersRepository")
 */
class Trousers extends Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="Waist", type="integer")
     */
    private $waist;

    /**
     * @var int
     *
     * @ORM\Column(name="Length", type="integer")
     */
    private $length;


    /**
     * Set waist
     *
     * @param integer $waist
     *
     * @return Trousers
     */
    public function setWaist($waist)
    {
        $this->waist = $waist;

        return $this;
    }

    /**
     * Get waist
     *
     * @return int
     */
    public function getWaist()
    {
        return $this->waist;
    }

    /**
     * Set length
     *
     * @param integer $length
     *
     * @return Trousers
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    public static function getAvailableFilters()
    {
        return array_merge(parent::getAvailableFilters(), ['waistMin', 'waistMax', 'lengthMin', 'lengthMax']);
    }
}

