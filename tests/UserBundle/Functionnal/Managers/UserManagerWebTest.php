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

    public function testUserCanBeValidated()
    {
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
