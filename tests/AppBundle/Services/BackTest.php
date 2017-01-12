<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;

// Service
use AppBundle\Services\Back;

// Entities
use AppBundle\Entity\Tricks;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;

/**
 * Class BackTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class BackTest extends KernelTestCase
{
    /**
     * @var Back
     */
    private $back;

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
        // Instantiate all the services.
        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->back = static::$kernel->getContainer()->get('app.back');

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

        $this->workflow->apply($tricks, 'start_phase');
        $this->workflow->apply($tricks, 'validation_phase');

        // Persist after relations.
        $this->doctrine->persist($tricks);
        $this->doctrine->flush();
    }

    /**
     * Test if the back service can be found and if he's the right class.
     */
    public function testBackServiceIsFound()
    {
        if (is_object($this->back)) {
            $this->assertInstanceOf(Back::class, $this->back);
        }
    }

    /**
     * Test if the return of the "entity found method" is correct.
     */
    public function testBackReturnEntityMethod()
    {
        if (is_object($this->back) && $this->back instanceof Back) {
            // Store the result to test the class.
            $trick = $this->back->getTricksByName('Backflip');
            $this->assertInstanceOf(
                Tricks::class,
                $trick
            );

            // Store the return to test the value passed through an array.
            $commentaries = $this->back->getCommentariesByTricks('Backflip');
            foreach ($commentaries as $commentary) {
                $this->assertInstanceOf(
                    Tricks::class,
                    $commentary->getTricks()
                );
            }

            // Store the return to test the value passed through the getters.
            $tricks = $this->back->getTricksByName('Backflip');
            $this->assertEquals(
                'Backflip',
                $tricks->getName()
            );
        }
    }

    /**
     * Test if the app.back service can validate a trick using his name.
     */
    public function testTricksValidationMethod()
    {
        $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                ->findOneBy(['name' => 'Backflip']);
        $this->workflow->apply($trick, 'start_phase');

        if (is_object($this->back) && $this->back instanceof Back) {
            // Validate the tricks find earlier.
            $this->back->validateTricks($trick->getName());

            $this->assertTrue($trick->getValidated());
        }
    }

    /**
     * Test if the app.back service can validate a trick using his name.
     */
    public function testTricksValidationMethodFail()
    {
        $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                ->findOneBy(['name' => 'Backflip']);
        $this->workflow->apply($trick, 'start_phase');

        if (is_object($this->back) && $this->back instanceof Back) {
            // Validate the tricks find earlier.
            $this->back->validateTricks($trick);
            // Test if the exception is thrown using only class.
            $this->expectException(\LogicException::class);
        }
    }

    /**
     * Test if the app.back service can refuse a validation using the Trick name.
     */
    public function testTricksNoValidationMethod()
    {
        $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                ->findOneBy(['name' => 'Backflip']);

        if (is_object($this->back) && $this->back instanceof Back) {
            // Refuse the tricks find earlier.
            $this->back->refuseTricks($trick->getName());

            $this->assertFalse($trick->getValidated());
        }
    }

    /**
     * Test if the app.back method who delete all the commentaries
     * linked to a trick work.
     */
    public function testCommentariesDeletingMethod()
    {
        if (is_object($this->back) && $this->back instanceof Back) {
            // Store the result to test the class.
            $this->back->deleteCommentaries('Backflip');

            // Find all the commentaries using tricks name.
            $commentary = $this->back->getCommentariesByTricks('Backflip');

            if (is_array($commentary)) {
                foreach ($commentary as $cmt) {
                    $this->assertInstanceOf(
                        Tricks::class,
                        $cmt->getTricks()
                    );
                }
            }
        }
    }

    /**
     * Test if the app.back method who delete a commentary linked to a tricks
     * using his id and the tricks name works.
     */
    public function testCommentaryDeletingByTricksMethod()
    {
        if (is_object($this->back) && $this->back instanceof Back) {
            // Store the result to test the class.
            $this->back->deleteCommentary('Backflip', 2);
            // Find a single commentary using tricks name and commentary id.
            // $this->assertNull(
            //    $this->back->getCommentaryByTricks(
            //        'Backflip',
            //        2
            //   )
            //);
        }
    }

    /**
     * Test if the app.back method for deleting a tricks using his name works.
     */
    public function testTricksSuppressionMethod()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy(['name' => 'Backflip']);

        if (is_object($this->back) && $this->back instanceof Back) {
            // Delete the tricks using his name
            $this->back->deleteTricks($tricks->getName());

            // Test if the method return the right class.
            $this->returnValue(RedirectResponse::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->clear(User::class);
        $this->doctrine->clear(Tricks::class);
        $this->doctrine->close();
        $this->doctrine = null;
    }
}
