<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class IndexControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class IndexControllerTest extends WebTestCase
{
    /**
     * Test if the index method return the homepage.
     */
    public function testIndexAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Snowtricks")')->count()
        );
    }

    /**
     * Test if the tricks method return the tricks page.
     */
    public function testTricksAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/tricks');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("tricks")')->count()
        );
    }

    /**
     * Test if the community method return the community page.
     */
    public function testCommunityAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/tricks');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("community")')->count()
        );
    }
}
