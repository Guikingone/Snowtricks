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
use Symfony\Component\HttpFoundation\Response;

// Controller
use UserBundle\Controller\UserController;

// Event
use UserBundle\Events\ConfirmedUserEvent;

// Listeners
use UserBundle\Listeners\RegisterListeners;

// Managers
use UserBundle\Managers\UserManager;

/**
 * Class UserControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserControllerTest extends WebTestCase
{
    /** @var null */
    private $client;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient([], [], [
            'PHP_AUTH_USER' => 'Nanon',
            'PHP_AUTH_PW' => 'lappd_dep',
        ]);
    }

    /**
     * Test if the profile of a User is accessible.
     *
     * @see UserController::profileAction()
     */
    public function testUserProfile()
    {
        $this->client->request('GET', '/community/profile/Guikingone');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a user can be locked using his name.
     *
     * @see UserController::userLockedAction()
     * @see UserManager::lockUser()
     */
    public function testAdminUserLockByName()
    {
        $this->client->request('GET', '/admin/user/lock/Loulier');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a user can be locked without the login phase.
     *
     * @see UserController::userLockedAction()
     * @see UserManager::lockUser()
     */
    public function testAdminUserLockByNameWithoutLogin()
    {
        // Surcharge the client headers to get rid of authenticated user.
        $this->client->request('GET', '/admin/user/lock/Delasource', [], [], [
            'PHP_AUTH_USER' => '',
            'PHP_AUTH_PW' => '',
        ]);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a user can be unlocked using his name.
     *
     * @see UserController::userUnlockedAction()
     */
    public function testAdminUserUnlockByName()
    {
        $this->client->request('GET', '/admin/user/unlock/Loulier');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the validation of a user using his token.
     *
     * @see UserController::validateUserAction()
     * @see UserManager::validateUser()
     * @see ConfirmedUserEvent
     * @see RegisterListeners::onValidatedUser()
     */
    public function testValidateUser()
    {
        $this->client->request('GET', '/community/users/validate/token_e61e26a5a42d3g9r4');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the validation of a user with bad token.
     *
     * @see UserController::validateUserAction()
     * @see UserManager::validateUser()
     * @see ConfirmedUserEvent
     * @see RegisterListeners::onValidatedUser()
     */
    public function testValidateUserWithBadToken()
    {
        $this->client->request('GET', '/community/users/validate/"%d"'. 2546813218);

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
