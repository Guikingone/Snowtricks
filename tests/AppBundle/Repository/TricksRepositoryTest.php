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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Tricks;
use UserBundle\Entity\User;

/**
 * Class TricksRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksRepositoryTest extends WebTestCase
{
    /**
     * Set up the entity used during this tests.
     */
    public function setUp()
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

        $kernel = self::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $doctrine->persist($tricks);
        $doctrine->flush();
    }

    /**
     * Test if the Tricks can be find by his name.
     */
    public function testTricksFindByName()
    {
        $kernel = self::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tricks = $doctrine->getRepository('AppBundle:Tricks')
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
        $kernel = self::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tricks = $doctrine->getRepository('AppBundle:Tricks')
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
        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setName('Loulier');
        $author->setFirstName('Guillaume');
        $author->setUsername('Guikingone');
        $author->setRoles('ROLE_ADMIN');

        $tricks = $doctrine->getRepository('AppBundle:Tricks')
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
        $kernel = self::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tricks = $doctrine->getRepository('AppBundle:Tricks')
                           ->findOneBy(array('name' => 'Backflip'));

        if (is_object($tricks)) {
            $this->assertInstanceOf(
                Tricks::class,
                $tricks
            );
        }

        if (is_object($tricks) && $tricks instanceof Tricks) {
            $doctrine->remove($tricks);
        }

        $this->assertEmpty($doctrine->getRepository('AppBundle:Tricks')->findAll());
    }
}
