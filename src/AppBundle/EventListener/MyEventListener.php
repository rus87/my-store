<?php
namespace AppBundle\EventListener;

use AppBundle\Event\MyEvent;

class MyEventListener
{
    public function onMyEvent(MyEvent $event)
    {
        dump($event->getData());
    }
}
