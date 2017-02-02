<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Managers;

use AppBundle\Entity\Tricks;
use AppBundle\Managers\CommentaryManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CommentaryManagerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryManagerTest extends KernelTestCase
{
    /**
     * @var CommentaryManager
     */
    private $manager;

    /** {@inheritdoc} */
    public function setUp()
    {
        // Instantiate all the services.
        self::bootKernel();
        $this->manager = static::$kernel->getContainer()->get('app.commentary_manager');
    }

    /**
     * Test if the commentary manager can be found and if he's the right class.
     */
    public function testBackServiceIsFound()
    {
        if (is_object($this->manager)) {
            $this->assertInstanceOf(
                CommentaryManager::class,
                $this->manager
            );
        }
    }

    /**
     * Test if a commentary can be found using tricks name and his id.
     */
    public function testCommentaryIsFoundByTricks()
    {
        if (is_object($this->manager)
            && $this->manager instanceof CommentaryManager) {
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
        if (is_object($this->manager)
            && $this->manager instanceof CommentaryManager) {
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
     * Test if the app.back method who delete all the commentaries
     * linked to a trick work.
     */
    public function testCommentariesDeletingMethodWithBadArgument()
    {
        if (is_object($this->manager)
            && $this->manager instanceof CommentaryManager) {
            // Store the result to test the class.
            $this->manager->deleteCommentaries(5742);

            $this->setExpectedException(\InvalidArgumentException::class);
        }
    }

    /**
     * Test if the app.back method who delete a commentary linked to a tricks
     * using his id and the tricks name works.
     */
    public function testCommentaryDeletingByTricksMethod()
    {
        if (is_object($this->manager)
            && $this->manager instanceof CommentaryManager) {
            // Store the result to test the class.
            $this->manager->deleteCommentary('FrontGrab', 2);
            // Find a single commentary using tricks name and commentary id.
            $this->returnValue(RedirectResponse::class);
        }
    }
}
