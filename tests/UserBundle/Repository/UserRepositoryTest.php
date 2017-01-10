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

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// Entity
use UserBundle\Entity\User;

/**
 * Class UserRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserRepositoryTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $user = new User();
        $user->setFirstname('Arnaud');
        $user->setLastname('Tricks');
        $user->setBirthdate(new \DateTime());
        $user->setOccupation('Professional snowboarder');
        $user->setEmail('non@snowtricks.fr');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setToken('654a6d4dzd19de4yhqdf4af4a1fa66fa4');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setActive(true);

        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->doctrine->persist($user);
        $this->doctrine->flush();
    }

    /**
     * Test if the user can be found using his name.
     */
    public function testUserIsFoundByName()
    {
        $user = $this->doctrine->getRepository('UserBundle:User')
                               ->findOneBy(['lastname' => 'Tricks']);

        if (is_object($user)) {
            $this->assertInstanceOf(
                User::class,
                $user
            );
        }

        if (is_object($user) && $user instanceof User) {
            $this->assertEquals('Arnaud', $user->getFirstname());
            $this->assertEquals('Tricks', $user->getLastname());
            $this->assertEquals('Professional snowboarder', $user->getOccupation());
            $this->assertEquals('Nono', $user->getUsername());
            $this->assertEquals('non@snowtricks.fr', $user->getEmail());
            $this->assertContains('ROLE_ADMIN', $user->getRoles());
            $this->assertTrue($user->getValidated());
            $this->assertEquals('654a6d4dzd19de4yhqdf4af4a1fa66fa4', $user->getToken());
            $this->assertFalse($user->getLocked());
            $this->assertTrue($user->getActive());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->clear(User::class);
        $this->doctrine->close();
        $this->doctrine = null;
    }
}
