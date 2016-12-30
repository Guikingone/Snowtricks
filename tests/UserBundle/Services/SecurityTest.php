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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Services\Security;
use UserBundle\Entity\User;

/**
 * Class SecurityTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityTest extends WebTestCase
{
    /**
     * Set the entity for BDD.
     */
    public function setUp()
    {
        $user = new User();
        $user->setFirstName('Arnaud');
        $user->setLastName('Tricks');
        $user->setBirthDate('12-05-1978');
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setRoles('ROLE_ADMIN');
        $user->setLocked(false);

        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $doctrine->persist($user);
        $doctrine->flush();
    }

    /**
     * Test if the service is found and correct.
     */
    public function testServiceIsFound()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service)) {
            $this->assertInstanceOf(Security::class, $service);
        }
    }

    /**
     * Test if the form for creating the users is available.
     */
    public function testUserForm()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            $this->assertInstanceOf(
                FormView::class,
                $service->addUser(new Request())
            );
        }
    }

    /**
     * Test if all the users can be found.
     */
    public function testUserRecap()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            $this->assertArrayHasKey(
                'Arnaud',
                $service->getUsers()
            );
        }
    }

    /**
     * Test if a single user can be find using his name.
     */
    public function testUserIsFoundByName()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            $this->assertInstanceOf(
                User::class,
                $service->getUser('Arnaud')
            );
        }
    }

    /**
     * Test if all the Users can be found when they're not validated.
     */
    public function testUserIsNotValidated()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            // Store into an array the list of users.
            $user = $service->getUsersNotValidated();
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
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            // Store into an array the list of users.
            $user = $service->getUsersValidated();
            if (is_array($user)) {
                foreach ($user as $usr) {
                    $this->assertInstanceOf(
                        User::class, $usr
                    );
                    $this->assertTrue($usr->getValidated());
                }
            }
        }
    }

    /**
     * Test if the user can be locked using his lastname.
     */
    public function testUserLock()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            $this->assertInstanceOf(
                RedirectResponse::class,
                $service->lockUser('Tricks')
            );
        }
    }

    /**
     * Test if the user can be unlocked using his lastname.
     */
    public function testUserUnlock()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            $this->assertInstanceOf(
                RedirectResponse::class,
                $service->unlockUser('Tricks')
            );
        }
    }

    /**
     * Test if the service can find every users locked.
     */
    public function testFindUsersLocked()
    {
        $kernel = static::createKernel();

        $service = $kernel->getContainer()->get('app.security');

        if (is_object($service) && $service instanceof Security) {
            // Store the result into an array
            $users = $service->getLockedUsers();
            if (is_array($users)) {
                foreach ($users as $user) {
                    $this->assertInstanceOf(User::class, $user);
                    $this->assertTrue($user->getLocked());
                }
            }
        }
    }
}
