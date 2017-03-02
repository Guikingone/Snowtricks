<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Controllers\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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
        $this->client = static::createClient();
    }

    /**
     * Test if all the users can be found.
     */
    public function testAllUsersFound()
    {
        $this->client->request('GET', '/api/users');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testSingleUserFound()
    {
        $this->client->request('GET', '/api/user/1');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
