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
 * Class SecurityControllerTest
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
            'plainPassword' => 'Ie1FDLNNA@'
        ]);

        $this->assertEquals(
            Response::HTTP_CREATED,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the login method accept the request.
     */
    public function testApiLogin()
    {
        $this->client->request('POST', '/api/login', [
            'email' => 'nanarland@world.fr',
            'password' => 'Ie1FDLNNA@'
        ]);

        dump($this->client->getResponse());

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }
}