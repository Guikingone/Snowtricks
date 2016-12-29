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
}
