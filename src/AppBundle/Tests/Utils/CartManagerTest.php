<?php
namespace AppBundle\Tests\Utils;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use AppBundle\Utils\CartManager;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Utils\UserManager\UserManagerTest;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CartManagerTest extends WebTestCase
{
    /**
     * @var CartManager
     */
    private $cm;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Client
     */
    private $client;

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

        $this->logIn();
        $this->client->request('GET', '/');

        $this->testProduct = $this->em->getRepository('AppBundle:Product')->find(1111);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cm = null;
        $this->em = null;
        static::$kernel->shutdown();
    }

    public function testGetCart()
    {
        $this->assertInstanceOf(Cart::class, $this->cm->getCart());
    }

    public function testClearCart()
    {
        $cart = $this->cm->clearCart();
        $this->assertTrue($cart->isEmpty());
    }

    public function testPullProduct()
    {
        $product = $this->testProduct;
        $cart = $this->cm->pullProduct($product);
        $this->assertTrue($cart->getProducts()->contains($product));
    }

    public function testRemoveProduct()
    {
        $product = $this->testProduct;
        $cart = $this->cm->getCart();
        if(!$cart->getProducts()->contains($product)) {
            $this->cm->pullProduct($product);
        }
        $cart = $this->cm->removeProduct($product);
        $this->assertTrue(!$cart->getProducts()->contains($product));
    }

    public function testGetCartProducts()
    {
        $product = $this->testProduct;
        if(!$this->cm->getCart()->getProducts()->contains($product)) {
            $this->cm->pullProduct($product);
        }

        $this->assertTrue($this->cm->getCart()->getProducts()->contains($product));
    }

    public function testToggleProduct()
    {
        $product = $this->testProduct;
        if(!$this->cm->getCart()->getProducts()->contains($product)) {
            $this->cm->pullProduct($product);
        }
        $this->cm->toggleProduct($product);
        $this->assertTrue(!$this->cm->getCart()->getProducts()->contains($product));
        $this->cm->toggleProduct($product);
        $this->assertTrue($this->cm->getCart()->getProducts()->contains($product));
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
