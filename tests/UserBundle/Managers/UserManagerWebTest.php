<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Managers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UserBundle\Controller\UserController;
use UserBundle\Managers\UserManager;
use UserBundle\Services\Security;

// Entity
use UserBundle\Entity\User;

/**
 * Class UserManagerWebTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserManagerWebTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** @var UserManager */
    private $manager;

    /** @var Security */
    private $security;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();

        $this->manager = $this->client->getContainer()->get('user.user_manager');
        $this->security = $this->client->getContainer()->get('user.security');
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
     * Test if the user can be validated using his token.
     *
     * @see UserController::validateUserAction()
     * @see UserManager::validateUser()
     */
    public function testUserCanBeValidated()
    {
        $this->client->request('GET', '/community/users/validate/token_e61e26a5a42d3g9r4');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the user can be validated using a bad token.
     *
     * @see UserController::validateUserAction()
     * @see UserManager::validateUser()
     */
    public function testUserCanBeValidatedWithBadToken()
    {
        $this->client->request('GET', '/community/users/validate/2540');

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the user can be locked using his lastname.
     *
     * @see UserController::userLockedAction()
     * @see UserManager::lockUser()
     */
    public function testUserCanBeLocked()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/user/lock/Loulier');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if the user can be locked using his lastname.
     *
     * @see UserController::userLockedAction()
     * @see UserManager::lockUser()
     */
    public function testUserCanBeLockedWithoutLogin()
    {
        $this->client->request('GET', '/admin/user/lock/Loulier');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the user can be locked using his lastname.
     *
     * @see UserController::userLockedAction()
     * @see UserManager::lockUser()
     */
    public function testUserCanBeLockedWithBadInfos()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/user/lock/Etna');

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the user can be unlocked using his lastname.
     *
     * @see UserController::userUnlockedAction()
     * @see UserManager::unlockUser()
     */
    public function testUserCanBeUnlocked()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/user/unlock/Loulier');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if the user can be unlocked using his lastname.
     *
     * @see UserController::userUnlockedAction()
     * @see UserManager::unlockUser()
     */
    public function testUserCanBeUnlockedWithoutLogin()
    {
        $this->client->request('GET', '/admin/user/unlock/Loulier');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the user can be unlocked using his lastname.
     *
     * @see UserController::userUnlockedAction()
     * @see UserManager::unlockUser()
     */
    public function testUserCanBeUnlockedWithBadCredentials()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/user/unlock/Etna');

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
