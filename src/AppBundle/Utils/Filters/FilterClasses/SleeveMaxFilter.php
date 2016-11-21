<?php
namespace AppBundle\Utils\Filters\FilterClasses;

use AppBundle\Utils\Filters\AbstractFilter;

class SleeveMaxFilter extends AbstractFilter
{
    public function __construct($rawValue)
    {
        parent::__construct($rawValue);
        $this->id = 'sleeveMax';
    }

    public function setRawValue($input)
    {
        $input = (int)$input;
        $input == 0 ? $this->rawValue = null : $this->rawValue = $input;
    }

    public function setQueryValue()
    {
        $this->rawValue == null ? $this->queryValue = null :
            $this->queryValue = "p.sleeveLength < $this->rawValue";
    }

}