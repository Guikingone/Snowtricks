<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UserBundle\Services\Security;

// Entity
use UserBundle\Entity\User;

/**
 * Class SecurityTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityTest extends KernelTestCase
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * Set the entity for BDD.
     */
    public function setUp()
    {
        $user = new User();
        $user->setFirstname('Arnaud');
        $user->setLastname('Tricks');
        $user->setBirthdate(new \DateTime());
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEmail('contact@snowtricks.fr');
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setIsActive(true);

        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->doctrine->persist($user);
        $this->doctrine->flush();

        $this->security = static::$kernel->getContainer()->get('user.security');
    }

    /**
     * Test if the service is found and correct.
     */
    public function testServiceIsFound()
    {
        if (is_object($this->security)) {
            $this->assertInstanceOf(
                Security::class,
                $this->security
            );
        }
    }

    /**
     * Test if all the users can be found.
     */
    public function testUserRecap()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            // Store the result into an array.
            $users = $this->security->getUsers();

            foreach ($users as $user) {
                $this->assertInstanceOf(User::class, $user);
            }
        }
    }

    /**
     * Test if a single user can be find using his name.
     */
    public function testUserIsFoundByName()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            $this->assertInstanceOf(
                User::class,
                $this->security->getUser('Tricks')
            );
        }
    }

    /**
     * Test if all the Users can be found when they're not validated.
     */
    public function testUserIsNotValidated()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            // Store into an array the list of users.
            $user = $this->security->getUsersNotValidated();
            if (is_array($user)) {
                foreach ($user as $usr) {
                    $this->assertInstanceOf(
                        User::class, $usr
                    );
                    $this->assertEquals(false, $usr->getValidated());
                }
            }
        }
    }

    /**
     * Test if all the Users can be found when they're validated.
     */
    public function testUserIsValidated()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            // Store into an array the list of users.
            $user = $this->security->getUsersValidated();
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
     * Test if the user can be locked using his lastname.
     */
    public function testUserLock()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            $this->security->lockUser('Tricks');

            $user = $this->security->getUser('tricks');

            $this->assertTrue($user->getLocked());
        }
    }

    /**
     * Test if the user can be unlocked using his lastname.
     */
    public function testUserUnlock()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            $this->security->unlockUser('Tricks');

            $user = $this->security->getUser('Tricks');

            $this->assertFalse($user->getLocked());
        }
    }

    /**
     * Test if the service can find every users locked.
     */
    public function testFindUsersLocked()
    {
        if (is_object($this->security) && $this->security instanceof Security) {
            // Store the result into an array
            $users = $this->security->getLockedUsers();
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
        if (is_object($this->security) && $this->security instanceof Security) {
            // Store the result into an array
            $users = $this->security->getUnlockedUsers();
            if (is_array($users)) {
                foreach ($users as $user) {
                    $this->assertInstanceOf(User::class, $user);
                    $this->assertFalse($user->getLocked());
                }
            }
        }
    }
}
