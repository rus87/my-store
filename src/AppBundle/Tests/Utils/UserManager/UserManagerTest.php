<?php
namespace AppBundle\Tests\Utils\UserManager;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\BrowserKit\Cookie;
use AppBundle\Utils\UserManager\UserManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserManagerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AuthorizationChecker
     */
    private $authChecker;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var Client
     */
    private $client;

    protected function setUp()
    {
        self::bootKernel();

        $this->client = static::createClient();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->authChecker = static::$kernel->getContainer()
            ->get('security.authorization_checker');

        $this->tokenStorage = static::$kernel->getContainer()
            ->get('security.token_storage');

    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }

    public function testGetCurrentUser()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/product/add');

        $userManager = new UserManager($this->em, $this->authChecker, $this->tokenStorage);
        $result = $userManager->getCurrentUser();
        $this->assertInstanceOf(User::class, $result);
    }


    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';
        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['email' => 'rus2_@rambler.ru']);

        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
