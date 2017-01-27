<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Managers;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UserBundle\Entity\User;
use UserBundle\Managers\UserManager;

/**
 * Class UserManagerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserManagerTest extends KernelTestCase
{
    /**
     * @var UserManager
     */
    private $manager;

    /** {@inheritdoc} */
    public function setUp()
    {
        self::bootKernel();
        $this->manager = static::$kernel->getContainer()->get('user.user_manager');
    }

    /**
     * Test if the manager is the right class instance.
     */
    public function testManagerIsFound()
    {
        if (is_object($this->manager)) {
            $this->assertInstanceOf(
                UserManager::class,
                $this->manager
            );
        }
    }

    /**
     * Test if all the users can be found using the manager and
     * if the all the users found are the instance
     * of the right class.
     */
    public function testServiceReturnAllUsers()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            $users = $this->manager->getUsers();

            if (is_array($users)) {
                foreach ($users as $user) {
                    $this->assertInstanceOf(
                        User::class,
                        $user
                    );
                }
            }
        }
    }

    /**
     * Test if a single user can be find using his name.
     */
    public function testUserIsFoundByName()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            $this->assertInstanceOf(
                User::class,
                $this->manager->getUser('Duchemin')
            );
        }
    }

    /**
     * Test if all the Users can be found when they're not validated.
     */
    public function testUserIsNotValidated()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            // Store into an array the list of users.
            $user = $this->manager->getUsersNotValidated();
            if (is_array($user)) {
                foreach ($user as $usr) {
                    $this->assertInstanceOf(
                        User::class, $usr
                    );
                    $this->assertFalse($usr->getValidated());
                }
            }
        }
    }

    /**
     * Test if all the Users can be found when they're validated.
     */
    public function testUserIsValidated()
    {
        if (is_object($this->manager) && $this->manager instanceof UserManager) {
            // Store into an array the list of users.
            $user = $this->manager->getUsersValidated();
            if (is_array($user)) {
                foreach ($user as $usr) {
                    if ($usr->getValidated()) {
                        $this->assertInstanceOf(
                            User::class, $usr
                        );
                        $this->assertTrue($usr->getValidated());
                    }
                }
            }
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
