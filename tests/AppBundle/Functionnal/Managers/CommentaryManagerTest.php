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

use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;
use AppBundle\Managers\CommentaryManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;

/**
 * Class CommentaryManagerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryManagerTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var CommentaryManager
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

        $this->manager = static::$kernel->getContainer()->get('app.commentary_manager');

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

        $commentary = new Commentary();
        $commentary->setTricks($tricks);
        $commentary->setAuthor($author);
        $commentary->setContent('Hey !');
        $commentary->setPublicationDate(new \DateTime());

        $this->workflow->apply($tricks, 'start_phase');
        $this->workflow->apply($tricks, 'validation_phase');

        $this->workflow->apply($tricksII, 'start_phase');
        $this->workflow->apply($tricksII, 'validation_phase');

        // Persist after relations.
        $this->doctrine->persist($tricks);
        $this->doctrine->persist($tricksII);
        $this->doctrine->persist($commentary);
        $this->doctrine->flush();
    }

    /**
     * Test if the commentary manager can be found and if he's the right class.
     */
    public function testBackServiceIsFound()
    {
        if (is_object($this->manager)) {
            $this->assertInstanceOf(CommentaryManager::class, $this->manager);
        }
    }

    /**
     * Test if a commentary can be found using tricks name and his id.
     */
    public function testCommentaryIsFoundByTricks()
    {
        if (is_object($this->manager) && $this->manager instanceof CommentaryManager) {
            $this->manager->getCommentaryByTricks('Backflip', 2);

            $this->returnValue(array());
        }
    }

    /**
     * Test if the app.back method who delete all the commentaries
     * linked to a trick work.
     */
    public function testCommentariesDeletingMethod()
    {
        if (is_object($this->manager) && $this->manager instanceof CommentaryManager) {
            // Store the result to test the class.
            $this->manager->deleteCommentaries('Backflip');

            // Find all the commentaries using tricks name.
            $commentary = $this->manager->getCommentariesByTricks('Backflip');

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
        if (is_object($this->manager) && $this->manager instanceof CommentaryManager) {
            // Store the result to test the class.
            $this->manager->deleteCommentary('Backflip', 2);
            // Find a single commentary using tricks name and commentary id.
            $this->returnValue(RedirectResponse::class);
        }
    }
}
