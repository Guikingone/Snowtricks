<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Functionnal\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class IndexControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test the indexAction.
     */
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test the tricksAction.
     */
    public function testTricks()
    {
        $client = static::createClient();

        $client->request('GET', '/tricks');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test the tricksDetailsAction.
     */
    public function testTricksDetails()
    {
        $client = static::createClient();

        $client->request('GET', '/tricks/Backflip');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
