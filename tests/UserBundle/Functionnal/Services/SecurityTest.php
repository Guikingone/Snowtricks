<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Functionnal\Services;

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
        $user->setActive(true);

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
}
