<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Controller\CartController;
use Symfony\Component\HttpKernel\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Utils\CartManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use AppBundle\Entity\Product;

class CartControllerTest extends WebTestCase
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CartManager
     */
    private $cm;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Product
     */
    private $testProduct;

    protected function setUp()
    {
        self::bootKernel();
        $this->client = static::createClient();

        $this->cm = static::$kernel->getContainer()
            ->get('cart_manager');

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->testProduct = $this->em->getRepository('AppBundle:Product')->find(1111);

        $this->logIn();
        $this->client->request('GET', '/');
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cm = null;
        $this->em = null;
        static::$kernel->shutdown();
    }

    public function testShowCartAction()
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/cart');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Shopping Cart")')->count());
    }

    public function testRemoveProductAction()
    {
        $product = $this->testProduct;
        $cart = $this->cm->getCart();
        $this->client->request('GET', sprintf('/cart/remove/%d.html', $product->getId()));
        $this->assertFalse($this->cm->getCartProducts()->contains($product));
    }

    public function testUpdateAction()
    {
        $client = $this->client;
        $product = $this->testProduct;
        $prodId = $product->getId();

        $client->request('GET', '/cart/update');
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->cm->removeProduct($product);
        $client->request('GET', sprintf('/cart/update/add/%d', $product->getId()));
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegexp("/.+:$prodId.+/", $client->getResponse()->getContent());


        $client->request('GET', "/cart/update/remove/$prodId");
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNotRegexp("/.+:$prodId.+/", $client->getResponse()->getContent());

        $client->request('GET', '/cart/update/add/9999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $contains = $this->cm->getCart()->getProducts()->contains($product);
        $client->request('GET', sprintf('/cart/update/toggle/%d', $product->getId()));
        if($contains) {
            $this->assertNotRegexp("/.+:$prodId.+/", $client->getResponse()->getContent());
        } else {
            $this->assertRegexp("/.+:$prodId.+/", $client->getResponse()->getContent());
        }
    }


    public function testClearAction()
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/cart/clear');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($this->cm->getCart()->isEmpty());
    }


    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';
        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['email' => 'rus2_@rambler.ru']);
        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $sesCookie = new Cookie($session->getName(), $session->getId());

        $this->client->getCookieJar()->set($sesCookie);
    }

}
