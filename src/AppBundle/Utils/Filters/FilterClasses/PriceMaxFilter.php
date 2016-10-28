<?php
namespace AppBundle\Utils\Filters\FilterClasses;

use AppBundle\Utils\Filters\AbstractFilter;
use AppBundle\Utils\CurrencyManager;

class PriceMaxFilter extends AbstractFilter
{
    private $cm;

    public function __construct($rawValue, CurrencyManager $cm)
    {
        $this->cm = $cm;
        parent::__construct($rawValue);
        $this->id = 'priceMax';
    }

    public function setRawValue($input)
    {
        $input = (float)$input;
        $input == 0 ? $this->rawValue = null : $this->rawValue = $input;
    }

    public function setQueryValue()
    {
        $price = $this->rawValue;
        if($price == null)
            $this->queryValue = null;
        else{
            $price = round($price / $this->cm->getClientCurrency()->getRatio(), 2);
            $this->queryValue = "p.price < $price";
        }
    }
}