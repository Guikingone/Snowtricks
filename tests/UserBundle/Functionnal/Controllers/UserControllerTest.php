<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Functionnal\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class UserControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserControllerTest extends WebTestCase
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
     * Test if the profile of a User is accessible.
     */
    public function testUserProfile()
    {
        $this->logIn();

        $this->client->request('GET', '/community/profile/Guikingone');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
