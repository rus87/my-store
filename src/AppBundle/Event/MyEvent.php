<?php
namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MyEvent extends Event
{
    private $data;

    public function __construct($input)
    {
        $this->data = $input;
    }

    public function getData()
    {
        return $this->data;
    }
}