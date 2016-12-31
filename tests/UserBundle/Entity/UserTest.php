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
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);

        $this->assertEquals('Arnaud', $user->getFirstName());
        $this->assertEquals('Tricks', $user->getLastName());
        $this->assertEquals('12-05-1978', $user->getBirthDate());
        $this->assertEquals('Professional snowboarder', $user->getOccupation());
        $this->assertEquals('Nono', $user->getUsername());
        $this->assertEquals('Lk__DTHE', $user->getPassword());
        $this->assertArrayHasKey('ROLE_ADMIN', $user->getRoles());
        $this->assertEquals('dd21498e61e26a5a42d3g9r4z2a364f2s3a2', $user->getToken());
        $this->assertTrue($user->getValidated());
        $this->assertFalse($user->getLocked());
    }
}
