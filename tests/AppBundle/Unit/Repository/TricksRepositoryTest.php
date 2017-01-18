<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Unit\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;

// Entity
use AppBundle\Entity\Tricks;
use UserBundle\Entity\User;

/**
 * Class TricksRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksRepositoryTest extends KernelTestCase
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
        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Test if the Tricks can be find by his name.
     */
    public function testTricksFindByName()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy([
                                     'name' => 'Backflip',
                                 ]);

        if (is_object($tricks)) {
            $this->assertEquals('Backflip', $tricks->getName());
            $this->assertInstanceOf(User::class, $tricks->getAuthor());
            $this->assertContains('Flip', $tricks->getGroups());
            $this->assertEquals('A simple backflip content ...', $tricks->getResume());
            $this->assertEquals(true, $tricks->getPublished());
            $this->assertEquals(true, $tricks->getValidated());
        }
    }

    /**
     * Test if the different Tricks can be found by the group.
     */
    public function testTricksFindByGroup()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findBy([
                                     'groups' => 'Flip',
                                 ]);

        if (is_array($tricks)) {
            foreach ($tricks as $trick) {
                $this->assertContains(
                    'Flip',
                    $trick->getGroups()
                );
            }
        }
    }

    /**
     * Test if a Tricks can be found using the author.
     */
    public function testTricksFindByAuthor()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setLastname('Loulier');
        $author->setFirstname('Guillaume');
        $author->setUsername('Guikingone');
        $author->setRoles(['ROLE_ADMIN']);

        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy([
                                     'author' => $author,
                                 ]);

        if (is_object($tricks)) {
            $this->assertInstanceOf(
                Tricks::class,
                $tricks
            );
        }

        if (is_object($tricks) && $tricks instanceof Tricks) {
            $this->assertEquals($author, $tricks->getAuthor());
        }
    }

    /**
     * Test if a Trick can be found by his name and remove.
     */
    public function testTricksSuppression()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy([
                                     'name' => 'Backflip',
                                 ]);

        if (is_object($tricks)) {
            $this->assertInstanceOf(
                Tricks::class,
                $tricks
            );
        }

        if (is_object($tricks) && $tricks instanceof Tricks) {
            $this->doctrine->remove($tricks);
        }

        // Check if the tricks is in the array of result.
        $this->assertNotContains(
            $tricks->getName(),
            $this->doctrine->getRepository('AppBundle:Tricks')->findAll()
        );
    }
}
