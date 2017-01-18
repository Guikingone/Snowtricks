<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Functionnal\Managers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UserBundle\Managers\UserManager;
use UserBundle\Services\Security;

// Entity
use UserBundle\Entity\User;

/**
 * Class UserManagerWebTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserManagerWebTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /**
     * @var UserManager
     */
    private $manager;

    /**
     * @var Security
     */
    private $security;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();

        $this->manager = $this->client->getContainer()->get('user.user_manager');
        $this->security = $this->client->getContainer()->get('user.security');
    }

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

    public function testUserCanBeUnlocked()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/admin');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        if ($this->client->getResponse()->getStatusCode() === 200) {
            $request = $this->client->request('GET', '/admin/users/unlock/Tricks');
            $this->assertEquals(302, $request->getResponse()->getStatusCode());
        }
    }

    /**
     * Test if the user can be locked using his lastname.
     */
    public function testUserLock()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            $this->manager->lockUser('Tricks');

            $user = $this->manager->getUser('tricks');

            $this->assertTrue($user->getLocked());
        }
    }

    /**
     * Test if the user can be unlocked using his lastname.
     */
    public function testUserUnlock()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            $this->manager->unlockUser('Tricks');

            $user = $this->manager->getUser('Tricks');

            $this->assertFalse($user->getLocked());
        }
    }

    /**
     * Test if the service can find every users locked.
     */
    public function testFindUsersLocked()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            // Store the result into an array
            $users = $this->manager->getLockedUsers();
            if (is_array($users)) {
                foreach ($users as $user) {
                    $this->assertInstanceOf(User::class, $user);
                    $this->assertTrue($user->getLocked());
                }
            }
        }
    }

    /**
     * Test if the service can find every users unlocked.
     */
    public function testFindUsersUnLocked()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            // Store the result into an array
            $users = $this->manager->getUnlockedUsers();
            if (is_array($users)) {
                foreach ($users as $user) {
                    $this->assertInstanceOf(User::class, $user);
                    $this->assertFalse($user->getLocked());
                }
            }
        }
    }
}
