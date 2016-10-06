<?php

namespace AppBundle\Entity\Products;

use Doctrine\ORM\Mapping as ORM;

/**
 * Blouse
 *
 * @ORM\Table(name="products\blouse")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Products\BlouseRepository")
 */
class Blouse extends Sweater
{

}

