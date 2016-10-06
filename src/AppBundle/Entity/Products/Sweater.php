<?php

namespace AppBundle\Entity\Products;

use AppBundle\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Sweater
 *
 * @ORM\Table(name="sweater")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Products\SweaterRepository")
 */
class Sweater extends Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="SleeveLength", type="integer")
     */
    private $sleeveLength;

    /**
     * @var string
     *
     * @ORM\Column(name="Composition", type="string", length=100, nullable=true)
     */
    private $composition;


    /**
     * Set sleeveLength
     *
     * @param string $sleeveLength
     *
     * @return Sweater
     */
    public function setSleeveLength($sleeveLength)
    {
        $this->sleeveLength = $sleeveLength;

        return $this;
    }

    /**
     * Get sleeveLength
     *
     * @return string
     */
    public function getSleeveLength()
    {
        return $this->sleeveLength;
    }

    /**
     * Set composition
     *
     * @param string $composition
     *
     * @return Sweater
     */
    public function setComposition($composition)
    {
        $this->composition = $composition;

        return $this;
    }

    /**
     * Get composition
     *
     * @return string
     */
    public function getComposition()
    {
        return $this->composition;
    }
}

