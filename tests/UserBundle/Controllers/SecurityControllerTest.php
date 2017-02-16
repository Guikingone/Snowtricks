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

// Controllers
use UserBundle\Controller\SecurityController;

// Service
use UserBundle\Events\UserRegisteredEvent;
use UserBundle\Listeners\RegisterListeners;
use UserBundle\Services\Security;

/**
 * Class SecurityControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityControllerTest extends WebTestCase
{
    /** @var null */
    private $client;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Test the registerAction.
     *
     * @see SecurityController::registerAction()
     * @see Security::registerUser()
     * @see UserRegisteredEvent
     * @see RegisterListeners::onUserRegistered()
     */
    public function testRegister()
    {
        $crawler = $this->client->request('GET', '/community/register');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['register[email]'] = 'contact@world.com';
            $form['register[username]'] = 'Esky';
            $form['register[plainPassword][first]'] = 'LBG,LDTH';
            $form['register[plainPassword][second]'] = 'LBG,LDTH';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_OK,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test the loginAction.
     *
     * @see SecurityController::loginAction()
     * @see Security::loginUser()
     */
    public function testLogin()
    {
        $crawler = $this->client->request('GET', '/community/login');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['_username'] = 'Guikingone';
            $form['_password'] = 'Lk__DTHE';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_FOUND,
                $this->client->getResponse()->getStatusCode()
            );

            $crawler = $this->client->followRedirect();

            $this->assertEquals(
                Response::HTTP_OK,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test the forgotPasswordAction.
     *
     * @see SecurityController::forgotPasswordAction()
     * @see Security::forgotPassword()
     */
    public function testForgotPassword()
    {
        $crawler = $this->client->request('GET', '/community/password/forgot');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['forgot_password[email]'] = 'guik@guillaumeloulier.fr';
            $form['forgot_password[username]'] = 'Guikingone';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_OK,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test the forgotPasswordAction with bad credentials.
     *
     * @see SecurityController::forgotPasswordAction()
     * @see Security::forgotPassword()
     */
    public function testForgotPasswordBadCredentials()
    {
        $crawler = $this->client->request('GET', '/community/password/forgot');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['forgot_password[email]'] = 'contact@world.fr';
            $form['forgot_password[username]'] = 'Anna';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }
}
