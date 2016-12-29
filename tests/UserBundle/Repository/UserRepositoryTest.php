<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Entity\User;

/**
 * Class UserRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserRepositoryTest extends WebTestCase
{
    /**
     * Set the entity in the BDD.
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

        $kernel = self::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $doctrine->persist($user);
        $doctrine->flush();
    }

    /**
     * Test if the Entity can be found using his name.
     */
    public function testEntityIsFoundByName()
    {
        $kernel = static::createKernel();

        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $user = $doctrine->getRepository('UserBundle:User')->findOneBy(['firstname' => 'Arnaud']);

        if (is_object($user) && $user instanceof User) {
            $this->assertEquals('Arnaud', $user->getFirstName());
            $this->assertEquals('Tricks', $user->getLastName());
            $this->assertEquals('12-05-1978', $user->getBirthDate());
            $this->assertEquals('Professional snowboarder', $user->getOccupation());
            $this->assertEquals('Nono', $user->getUsername());
            $this->assertEquals('Lk__DTHE', $user->getPassword());
            $this->assertArrayHasKey('ROLE_ADMIN', $user->getRoles());
        }
    }
}
