<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;
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
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setName('Loulier');
        $author->setFirstName('Guillaume');
        $author->setUsername('Guikingone');
        $author->setRoles('ROLE_ADMIN');

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate('26/12/2016');
        $tricks->setAuthor($author);
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple backflip content ...');
        $tricks->setPublished(true);
        $tricks->setValidated(true);

        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->doctrine->persist($tricks);
        $this->doctrine->flush();
    }

    /**
     * Test if the Tricks can be find by his name.
     */
    public function testTricksFindByName()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy(array('name' => 'Backflip'));

        if (is_object($tricks)) {
            $this->assertEquals('Backflip', $tricks->getName());
            $this->assertEquals('26/12/2016', $tricks->getCreationDate());
            $this->assertEquals('Guik', $tricks->getAuthor());
            $this->assertArrayHasKey('Flip', $tricks->getGroups());
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
                                 ->findBy(array('group' => 'Flip'));

        if (is_array($tricks)) {
            foreach ($tricks as $trick) {
                $this->assertArrayHasKey('Flip', $trick->getGroup());
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
        $author->setName('Loulier');
        $author->setFirstName('Guillaume');
        $author->setUsername('Guikingone');
        $author->setRoles('ROLE_ADMIN');

        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy(['author' => $author]);

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
                                 ->findOneBy(array('name' => 'Backflip'));

        if (is_object($tricks)) {
            $this->assertInstanceOf(
                Tricks::class,
                $tricks
            );
        }

        if (is_object($tricks) && $tricks instanceof Tricks) {
            $this->doctrine->remove($tricks);
        }

        $this->assertEmpty($this->doctrine->getRepository('AppBundle:Tricks')->findAll());
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
