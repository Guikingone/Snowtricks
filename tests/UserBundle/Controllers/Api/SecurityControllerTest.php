<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Controllers\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Controller\Api\SecurityController;
use UserBundle\Services\Api\Security;

/**
 * Class SecurityControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityControllerTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Test if the register method work.
     *
     * @see SecurityController::registerAction()
     * @see Security::register()
     */
    public function testApiRegister()
    {
        $this->client->request('POST', '/api/register', [
            'email' => 'nanarland@world.fr',
            'username' => 'NanarLand',
            'plainPassword' => 'Ie1FDLNNA@',
        ]);

        $this->assertEquals(
            Response::HTTP_CREATED,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the register method work.
     *
     * @see SecurityController::registerAction()
     * @see Security::register()
     */
    public function testApiRegister_Failure_BadInput()
    {
        $this->client->request('POST', '/api/register', [
            'email' => 'nanarland@world.fr',
            'user' => 'NanarLand',
            'password' => 'Ie1FDLNNA@',
        ]);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the login method accept the request.
     *
     * @see SecurityController::loginAction()
     * @see Security::login()
     */
    public function testApiLogin()
    {
        $this->client->request('POST', '/api/login', [
            'username' => 'NanarLand',
            'password' => 'Ie1FDLNNA@',
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the login method accept the request with wrong input.
     *
     * @see SecurityController::loginAction()
     * @see Security::login()
     */
    public function testApiLogin_Failure_BadInput()
    {
        $this->client->request('POST', '/api/login', [
            'email' => 'nanarland@world.fr',
            'password' => 'Ie1FDLNNA',
        ]);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the forgot password action work with valid input.
     *
     * @see SecurityController::forgotPasswordAction()
     * @see Security::forgotPassword()
     */
    public function testApiForgotPassword()
    {
        $this->client->request('POST', '/api/forgot/password', [
            'email' => 'nanarland@world.fr',
            'username' => 'NanarLand',
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the forgot password action work with valid input.
     *
     * @see SecurityController::forgotPasswordAction()
     * @see Security::forgotPassword()
     */
    public function testApiForgotPassword_Failure_BadInfos()
    {
        $this->client->request('POST', '/api/forgot/password', [
            'email' => 'nanarland@world.fr',
            'username' => 'Gringuoli',
        ]);

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the forgot password action work with wrong input.
     *
     * @see SecurityController::forgotPasswordAction()
     * @see Security::forgotPassword()
     */
    public function testApiForgotPassword_Failure_BadInput()
    {
        $this->client->request('POST', '/api/forgot/password', [
            'email' => 'NanarLand',
            'username' => 'nanarland@world.fr',
        ]);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testApiValidateUser()
    {
        $this->client->request('GET', '/api/validate/profile/token_e61e26a5a42d3g9r4');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
