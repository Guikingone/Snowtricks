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

/**
 * Class SecurityControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test the registerAction.
     */
    public function testRegister()
    {
        $client = static::createClient();

        $client->request('GET', '/community/register');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test the loginAction.
     */
    public function testLogin()
    {
        $client = static::createClient();

        $client->request('GET', '/community/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test the profileAction.
     */
    public function testProfile()
    {
        $client = static::createClient();

        $client->request('GET', '/community/profile/Guik');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
