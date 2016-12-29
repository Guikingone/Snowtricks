<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use UserBundle\Entity\User;

/**
 * Class UserTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserTest extends TestCase
{
    /**
     * Test the boot of the Entity.
     */
    public function testEntityBoot()
    {
        $user = new User();

        $user->setFirstName('Arnaud');
        $user->setLastName('Tricks');
        $user->setBirthDate('12-05-1978');
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setRoles('ROLE_ADMIN');

        $this->assertEquals('Arnaud', $user->getFirstName());
        $this->assertEquals('Tricks', $user->getLastName());
        $this->assertEquals('12-05-1978', $user->getBirthDate());
        $this->assertEquals('Professional snowboarder', $user->getOccupation());
        $this->assertEquals('Nono', $user->getUsername());
        $this->assertEquals('Lk__DTHE', $user->getPassword());
        $this->assertArrayHasKey('ROLE_ADMIN', $user->getRoles());
    }
}
