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
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Test the indexAction.
     */
    public function testIndex()
    {
        $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the tricksAction.
     */
    public function testTricks()
    {
        $this->client->request('GET', '/tricks');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the tricksDetailsAction.
     */
    public function testTricksDetails()
    {
        $this->client->request('GET', '/tricks/Backflip');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a new Tricks can be added.
     */
    public function testsTricksAdd()
    {
        $crawler = $this->client->request('GET', '/admin/tricks/validate/Frontflip');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        if ($this->client->getResponse()->getStatusCode() === 200) {
            $form = $crawler->selectButton('submit')->form();

            $form['app_bundle_tricks_type[name]'] = 'Sideflip';
            $form['app_bundle_tricks_type[groups]'] = 'Flip';
            $form['app_bundle_tricks_type[resume'] = 'A new content about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        }
    }
}
