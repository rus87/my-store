<?php

namespace AppBundle\Utils\CrumbsGenerator;


class Crumb
{
    private $link;
    private $mark;
    private $isLast = false;

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     * @return Crumb
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @return mixed
     */
    public function getIsLast()
    {
        return $this->isLast;
    }

    /**
     * @param mixed $isLast
     * @return Crumb
     */
    public function setIsLast($isLast)
    {
        $this->isLast = $isLast;
        return $this;
    }

    /**
     * @param mixed $mark
     * @return Crumb
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
        return $this;
    }




}