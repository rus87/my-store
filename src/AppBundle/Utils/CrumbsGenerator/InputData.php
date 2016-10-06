<?php

namespace AppBundle\Utils\CrumbsGenerator;


class InputData
{
    /**
     * @var string
     */
    private $routeName;
    private $routeParams = [];
    private $mark;

    public function __construct($routeName, $routeParams = null, $mark = null)
    {
        $this->setRouteName($routeName);
        $this->setRouteParams($routeParams);
        $this->setMark($mark);
    }

    /**
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param mixed $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * @param array $routeParams
     */
    public function setRouteParams($routeParams)
    {
        $this->routeParams = $routeParams;
    }

    /**
     * @return mixed
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @param mixed $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

}

