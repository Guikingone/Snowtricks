<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UserBundle\Controller\UserController;

/**
 * Class UserControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserControllerTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /** Only for authentication purpose */
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';

        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Test if the profile of a User is accessible.
     *
     * @see UserController::profileAction()
     */
    public function testUserProfile()
    {
        $this->logIn();

        $this->client->request('GET', '/community/profile/Guikingone');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a user can be locked using his name.
     *
     * @see UserController::userLockedAction()
     */
    public function testAdminUserLockByName()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/user/lock/Loulier');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a user can be unlocked using his name.
     *
     * @see UserController::userUnlockedAction()
     */
    public function testAdminUserUnlockByName()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/user/unlock/Loulier');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the validate.
     *
     * @see UserController::validateUserAction()
     */
    public function testValidateUser()
    {
        $this->client->request('GET', '/community/users/validate/dd21498e61e26a5a42d3g9r4z2a364f2s3a2');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
