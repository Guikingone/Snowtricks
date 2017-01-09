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

// Entity
use AppBundle\Entity\Tricks;
use Symfony\Component\Workflow\Workflow;
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
     * @var Workflow
     */
    private $workflow;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->workflow = static::$kernel->getContainer()->get('workflow.tricks_process');

        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setLastname('Loulier');
        $author->setFirstname('Guillaume');
        $author->setUsername('Guikingone');
        $author->setBirthdate(new \DateTime());
        $author->setRoles(['ROLE_ADMIN']);
        $author->setOccupation('Rally Driver');
        $author->setEmail('guik@guillaumeloulier.fr');
        $author->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $author->setValidated(true);
        $author->setLocked(false);
        $author->setIsActive(true);

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate(new \DateTime());
        $tricks->setAuthor($author);
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple backflip content ...');
        $tricks->setPublished(true);
        $tricks->setValidated(true);

        // Apply workflow for entity state.
        $this->workflow->apply($tricks, 'start_phase');
        $this->workflow->apply($tricks, 'validation_phase');

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
                                 ->findBy(array('groups' => 'Flip'));

        if (is_array($tricks)) {
            foreach ($tricks as $trick) {
                $this->assertContains('Flip', $trick->getGroups());
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

        // Check if the tricks is in the array of result.
        $this->assertNotContains(
            $tricks->getName(),
            $this->doctrine->getRepository('AppBundle:Tricks')->findAll()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->clear(Tricks::class);
        $this->doctrine->clear(User::class);
        $this->doctrine->close();
        $this->doctrine = null;
    }
}
