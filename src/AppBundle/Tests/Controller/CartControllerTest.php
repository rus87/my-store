<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Controller\CartController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartControllerTest extends WebTestCase
{


    public function testUpdateAction()
    {
        $client = static::createClient();

        $client->request('GET', '/cart/update');
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());



        $client->request('GET', '/cart/update/add/1000');
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegexp('/.+:1000.+/', $client->getResponse()->getContent());


        $client->request('GET', '/cart/update/remove/1000');
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNotRegexp('/.+:1000.+/', $client->getResponse()->getContent());


        $client->request('GET', '/cart/update/add/9999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

}