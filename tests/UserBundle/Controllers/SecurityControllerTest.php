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
use UserBundle\Controller\SecurityController;

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
     * Test the registerAction.
     *
     * @see SecurityController::registerAction()
     */
    public function testRegister()
    {
        $this->client->request('GET', '/community/register');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the loginAction.
     *
     * @see SecurityController::loginAction()
     */
    public function testLogin()
    {
        $this->client->request('GET', '/community/login');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the forgotPasswordAction.
     *
     * @see SecurityController::forgotPasswordAction()
     */
    public function testForgotPassword()
    {
        $this->client->request('GET', '/community/password/forgot');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
