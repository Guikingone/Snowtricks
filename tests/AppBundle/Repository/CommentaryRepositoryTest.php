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

// Entities
use AppBundle\Entity\Commentary;

/**
 * Class CommentaryRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryRepositoryTest extends KernelTestCase
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
     * Test if all the commentaries are found.
     */
    public function testAllCommentaryIsFound()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->findAll();

        if (is_array($commentary)) {
            $this->assertNotNull($commentary);
        }
    }

    /**
     * Test if the commentary can be found using his id.
     */
    public function testCommentaryIsFoundById()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->findOneBy([
                                         'id' => 0,
                                     ]);

        if (is_object($commentary)) {
            $this->assertInstanceOf(
                Commentary::class,
                $commentary
            );
        }

        if (is_object($commentary) && $commentary instanceof Commentary) {
            $this->assertEquals(0, $commentary->getId());
            $this->assertEquals('A simple commentary', $commentary->getContent());
            $this->assertEquals('26-12-2016', $commentary->getCreationDate());
        }
    }

    /**
     * Test if a commentary can be found using his id and the tricks name.
     */
    public function testCommentaryIsFoundByTricksNameAndSelfId()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->getCommentaryByTricks('Backflip', 1);

        if (is_object($commentary)) {
            $this->assertInstanceOf(
                Commentary::class,
                $commentary
            );

            $this->assertEquals(1, $commentary->getId());
        }
    }

    /**
     * Same request as earlier but using the Doctrine repository this time.
     *
     * @see CommentaryRepositoryTest::testCommentaryIsFoundByTricksNameAndSelfId
     */
    public function testCommentaryIsFoundByTricksNameAndSelfIdUsingRepo()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->getCommentaryByTricks('Backflip', 1);

        if (is_object($commentary)) {
            $this->assertInstanceOf(
                Commentary::class,
                $commentary
            );

            $this->assertEquals(1, $commentary->getId());
        }
    }

    /**
     * Test if the commentaries can be found using tricks id.
     */
    public function testCommentaryIsFoundByTricksId()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->findBy([
                                         'tricks' => 0,
                                     ]);

        if (is_array($commentary)) {
            foreach ($commentary as $cmt) {
                $this->assertArrayHasKey(0, $cmt->getTricks());
            }
        }
    }

    /**
     * Test if a single commentary can be found using tricks id.
     */
    public function testSingleCommentaryIsFoundByTricksId()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->findOneBy([
                                         'tricks' => 0,
                                     ]);

        if (is_object($commentary)) {
            $this->assertInstanceOf(
                Commentary::class,
                $commentary
            );
        }

        if (is_object($commentary) && $commentary instanceof Commentary) {
            $this->assertArrayHasKey(0, $commentary->getTricks());
        }
    }

    /**
     * Test if the commentaries can be removed from the BDD and not find after it.
     */
    public function testCommentaryIsRemove()
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->findAll();

        foreach ($commentary as $cmt) {
            $this->doctrine->remove($cmt);
        }

        // try to find the entities after remove.
        $commentaries = $this->doctrine->getRepository('AppBundle:Commentary')
                                       ->findAll();

        $this->assertNotContains(Commentary::class, $commentaries);
    }
}
