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
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class TricksAdminControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksAdminControllerTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /** Only for authentication purpose */
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';

        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Test the validation of a tricks using his name.
     */
    public function testTricksValidation()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/tricks/validate/Frontflip');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the refuse process of a tricks using his name.
     */
    public function testTricksRefused()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/tricks/refuse/TruckDriver');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a tricks can be updated.
     */
    public function testTricksUpdated()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/admin/tricks/update/TruckDriver');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        if ($this->client->getResponse()->getStatusCode() === 200) {
            $form = $crawler->selectButton('submit')->form();

            $form['update_tricks[name]'] = 'Airflip';
            $form['update_tricks[groups]']->select('Flip');
            $form['update_tricks[resume]'] = 'A new content about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }
    }

    /**
     * Test the refuse process of a tricks using his name.
     */
    public function testTricksDeleted()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/tricks/delete/Bigfoot');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
