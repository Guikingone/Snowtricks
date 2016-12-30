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
        $user->setFirstName('Arnaud');
        $user->setLastName('Tricks');
        $user->setBirthDate('12-05-1978');
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setRoles('ROLE_ADMIN');
        $user->setValidated(true);
        $user->setToken('654a6d4dzd19de4yhqdf4af4a1fa66fa4');

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
                               ->findOneBy(['firstname' => 'Arnaud']);

        if (is_object($user)) {
            $this->assertInstanceof(
                User::class,
                $user
            );
        }

        if (is_object($user) && $user instanceof User) {
            $this->assertEquals('Arnaud', $user->getFirstName());
            $this->assertEquals('Tricks', $user->getLastName());
            $this->assertEquals('12-05-1978', $user->getBirthDate());
            $this->assertEquals('Professional snowboarder', $user->getOccupation());
            $this->assertEquals('Nono', $user->getUsername());
            $this->assertEquals('Lk__DTHE', $user->getPassword());
            $this->assertArrayHasKey('ROLE_ADMIN', $user->getRoles());
            $this->assertTrue($user->getValidated());
            $this->assertEquals('654a6d4dzd19de4yhqdf4af4a1fa66fa4', $user->getToken());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->close();
        $this->doctrine = null;
    }
}
