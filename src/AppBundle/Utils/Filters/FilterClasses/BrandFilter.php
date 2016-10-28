<?php
namespace AppBundle\Utils\Filters\FilterClasses;

use AppBundle\Utils\Filters\AbstractFilter;

class BrandFilter extends AbstractFilter
{
    public function __construct($rawValue)
    {

        parent::__construct($rawValue);
        $this->id = 'brand';
    }

    /**
     * @param array $input
     */
    public function setRawValue($input)
    {
        $this->rawValue = $input;
    }

    public function setQueryValue()
    {
        $brands = explode('.', $this->rawValue);
        $this->queryValue = '';
        $brandsCount = count($brands);
        for($i=0; $i<$brandsCount; $i++){
            $brands[$i] = (int)$brands[$i];
            $this->queryValue .= "p.brand = '$brands[$i]'";
            if($i < $brandsCount-1)
                $this->queryValue .= " OR ";
        }
    }

}