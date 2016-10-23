<?php
namespace AppBundle\Utils\Filters\FilterClasses;

use AppBundle\Utils\Filters\AbstractFilter;

class PriceMinFilter extends AbstractFilter
{
    public function __construct($rawValue)
    {
        parent::__construct($rawValue);
        $this->id = 'priceMin';
    }

    public function setRawValue($input)
    {
        $input = (float)$input;
        $input == 0 ? $this->rawValue = null : $this->rawValue = $input;
    }

    public function setQueryValue()
    {
        $this->rawValue == null ? $this->queryValue = null :
            $this->queryValue = "p.price > $this->rawValue";
    }

}