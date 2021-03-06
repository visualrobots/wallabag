<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Entity\Config;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Helper\Redirect;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $routerMock;

    /** @var Redirect */
    private $redirect;

    public function setUp()
    {
        $this->routerMock = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->routerMock->expects($this->any())
            ->method('generate')
            ->with('homepage')
            ->willReturn('homepage');

        $user = new User();
        $user->setName('youpi');
        $user->setEmail('youpi@youpi.org');
        $user->setUsername('youpi');
        $user->setPlainPassword('youpi');
        $user->setEnabled(true);
        $user->addRole('ROLE_SUPER_ADMIN');

        $config = new Config($user);
        $config->setTheme('material');
        $config->setItemsPerPage(30);
        $config->setReadingSpeed(1);
        $config->setLanguage('en');
        $config->setPocketConsumerKey('xxxxx');
        $config->setActionMarkAsRead(Config::REDIRECT_TO_CURRENT_PAGE);

        $user->setConfig($config);

        $this->token = new UsernamePasswordToken($user, 'password', 'key');
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($this->token);

        $this->redirect = new Redirect($this->routerMock, $tokenStorage);
    }

    public function testRedirectToNullWithFallback()
    {
        $redirectUrl = $this->redirect->to(null, 'fallback');

        $this->assertEquals('fallback', $redirectUrl);
    }

    public function testRedirectToNullWithoutFallback()
    {
        $redirectUrl = $this->redirect->to(null);

        $this->assertEquals($this->routerMock->generate('homepage'), $redirectUrl);
    }

    public function testRedirectToValidUrl()
    {
        $redirectUrl = $this->redirect->to('/unread/list');

        $this->assertEquals('/unread/list', $redirectUrl);
    }

    public function testWithNotLoggedUser()
    {
        $redirect = new Redirect($this->routerMock, new TokenStorage());
        $redirectUrl = $redirect->to('/unread/list');

        $this->assertEquals('/unread/list', $redirectUrl);
    }

    public function testUserForRedirectToHomepage()
    {
        $this->token->getUser()->getConfig()->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);

        $redirectUrl = $this->redirect->to('/unread/list');

        $this->assertEquals($this->routerMock->generate('homepage'), $redirectUrl);
    }
}
