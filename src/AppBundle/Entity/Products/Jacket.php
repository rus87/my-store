<?php

namespace AppBundle\Entity\Products;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Product;

/**
 * Jacket
 *
 * @ORM\Table(name="jacket")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Products\JacketRepository")
 */
class Jacket extends Product
{

    /**
     * @var int
     *
     * @ORM\Column(name="SleeveLength", type="integer")
     */
    protected $sleeveLength;

    /**
     * @var string
     *
     * @ORM\Column(name="Filling", type="string", length=100)
     */
    protected $filling;

    /**
     * @var string
     *
     * @ORM\Column(name="OuterMaterial", type="string", length=50)
     */
   protected $outerMaterial;

    /**
     * @var string
     *
     * @ORM\Column(name="Seasonality", type="string", length=50, nullable=true)
     */
    protected $seasonality;


    /**
     * Set sleeveLength
     *
     * @param integer $sleeveLength
     *
     * @return Jacket
     */
    public function setSleeveLength($sleeveLength)
    {
        $this->sleeveLength = $sleeveLength;

        return $this;
    }

    /**
     * Get sleeveLength
     *
     * @return int
     */
    public function getSleeveLength()
    {
        return $this->sleeveLength;
    }

    /**
     * Set filling
     *
     * @param string $filling
     *
     * @return Jacket
     */
    public function setFilling($filling)
    {
        $this->filling = $filling;

        return $this;
    }

    /**
     * Get filling
     *
     * @return string
     */
    public function getFilling()
    {
        return $this->filling;
    }

    /**
     * Set outerMaterial
     *
     * @param string $outerMaterial
     *
     * @return Jacket
     */
    public function setOuterMaterial($outerMaterial)
    {
        $this->outerMaterial = $outerMaterial;

        return $this;
    }

    /**
     * Get outerMaterial
     *
     * @return string
     */
    public function getOuterMaterial()
    {
        return $this->outerMaterial;
    }

    /**
     * Set seasonality
     *
     * @param string $seasonality
     *
     * @return Jacket
     */
    public function setSeasonality($seasonality)
    {
        $this->seasonality = $seasonality;

        return $this;
    }

    /**
     * Get seasonality
     *
     * @return string
     */
    public function getSeasonality()
    {
        return $this->seasonality;
    }
}

