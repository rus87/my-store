<?php
namespace AppBundle\Utils\Filters;

abstract class AbstractFilter implements FilterInterface
{
    protected $id;
    protected $rawValue;
    protected $queryValue;

    public function __construct($rawValue)
    {
        $this->setRawValue($rawValue);
        $this->setQueryValue();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * @return mixed
     */
    public function getQueryValue()
    {
        return $this->queryValue;
    }




}