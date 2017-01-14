<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Functionnal\Managers;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Workflow\Workflow;

// Manager
use AppBundle\Managers\TricksManager;

// Entity
use AppBundle\Entity\Tricks;
use UserBundle\Entity\User;

/**
 * Class TricksManagerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksManagerTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var TricksManager
     */
    private $manager;

    /**
     * @var Workflow
     */
    private $workflow;

    /** {@inheritdoc} */
    public function setUp()
    {
        // Instantiate all the services.
        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->manager = static::$kernel->getContainer()->get('app.tricks_manager');

        $this->workflow = static::$kernel->getContainer()->get('workflow.tricks_process');

        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Arnaud');
        $author->setLastname('Duchemin');
        $author->setLastname('Duduche');
        $author->setUsername('Nono');
        $author->setRoles(['ROLE_ADMIN']);
        $author->setBirthdate(new \DateTime());
        $author->setOccupation('Rally Driver');
        $author->setEmail('guik@guillaumeloulier.fr');
        $author->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $author->setValidated(true);
        $author->setLocked(false);
        $author->setActive(true);

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setAuthor($author);
        $tricks->setCreationDate(new \DateTime());
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple backflip content ...');
        $tricks->setPublished(true);
        $tricks->setValidated(true);

        $tricksII = new Tricks();
        $tricksII->setName('Frontflip');
        $tricksII->setAuthor($author);
        $tricksII->setCreationDate(new \DateTime());
        $tricksII->setGroups('Flip');
        $tricksII->setResume('A simple backflip content ...');
        $tricksII->setPublished(false);
        $tricksII->setValidated(false);

        $this->workflow->apply($tricks, 'start_phase');
        $this->workflow->apply($tricks, 'validation_phase');

        $this->workflow->apply($tricksII, 'start_phase');
        $this->workflow->apply($tricksII, 'validation_phase');

        // Persist after relations.
        $this->doctrine->persist($tricks);
        $this->doctrine->persist($tricksII);
        $this->doctrine->flush();
    }

    /**
     * Test if the tricks manager can be found and if he's the right class.
     */
    public function testBackServiceIsFound()
    {
        if (is_object($this->manager)) {
            $this->assertInstanceOf(TricksManager::class, $this->manager);
        }
    }

    /**
     * Test if the return of the "entity found method" is correct.
     */
    public function testTricksReturnMethod()
    {
        if (is_object($this->manager) && $this->manager instanceof TricksManager) {
            // Store the result to test the class.
            $trick = $this->manager->getTricksByName('Backflip');
            $this->assertInstanceOf(
                Tricks::class,
                $trick
            );
            $this->assertEquals(
                'Backflip',
                $trick->getName()
            );
        }
    }

    /**
     * Test if the app.back service can validate a trick using his name.
     */
    public function testTricksValidationMethod()
    {
        $trick = $this->manager->getTricksByName('Backflip');

        $this->workflow->apply($trick, 'start_phase');

        if (is_object($this->manager) && $this->manager instanceof TricksManager) {
            // Validate the tricks find earlier.
            $this->manager->validateTricks($trick->getName());

            $this->assertTrue($trick->getValidated());
        }
    }

    /**
     * Test if the app.back service can refuse a validation using the Trick name.
     */
    public function testTricksNoValidationMethod()
    {
        $trick = $this->manager->getTricksByName('Backflip');

        if (is_object($this->manager) && $this->manager instanceof TricksManager) {
            // Refuse the tricks find earlier.
            $this->manager->refuseTricks($trick->getName());

            $this->assertFalse($trick->getValidated());
        }
    }

    /**
     * Test if the app.back method for deleting a tricks using his name works.
     */
    public function testTricksSuppressionMethod()
    {
        $tricks = $this->manager->getTricksByName('Backflip');

        if (is_object($this->manager) && $this->manager instanceof TricksManager) {
            // Delete the tricks using his name
            $this->manager->deleteTricks($tricks->getName());

            // Test if the method return the right class.
            $this->returnValue(RedirectResponse::class);
        }
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->clear(User::class);
        $this->doctrine->clear(Tricks::class);
        $this->doctrine->close();
        $this->doctrine = null;
    }
}
