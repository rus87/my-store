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
        $this->rawValue = (string)$input;
    }

    public function setQueryValue()
    {
        $this->queryValue = "p.gender = '$this->rawValue'";
    }

}