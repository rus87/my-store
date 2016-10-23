<?php
namespace AppBundle\Utils\Filters;

interface FilterInterface
{
    /**
     * @param string $input
     */
    public function setRawValue($input);

    public function setQueryValue();

}