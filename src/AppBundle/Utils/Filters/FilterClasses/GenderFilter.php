<?php
namespace AppBundle\Utils\Filters\FilterClasses;

use AppBundle\Utils\Filters\AbstractFilter;

class GenderFilter extends AbstractFilter
{
    public function __construct($rawValue)
    {
        parent::__construct($rawValue);
        $this->id = 'gender';
    }

    public function setRawValue($input)
    {
        $this->rawValue = $input;
    }

    public function setQueryValue()
    {
        $this->rawValue == 'both' ? $this->queryValue = null :
            $this->queryValue = "p.gender = '$this->rawValue'";
    }

}