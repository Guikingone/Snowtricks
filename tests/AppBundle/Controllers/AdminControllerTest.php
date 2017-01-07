<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AdminControllerTest
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class AdminControllerTest extends WebTestCase
{
    /**
     * Test the adminAction.
     */
    public function testAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        // Test the redirection because of the security process.
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Test the adminTricksAction.
     */
    public function testAdminTricks()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/tricks');

        // Test the redirection because of the security process.
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }


    /**
     * Test the adminUsersAction.
     */
    public function testAdminUsers()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/users');

        // Test the redirection because of the security process.
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}