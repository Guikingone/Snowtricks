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
use AppBundle\Entity\tricks;
use AppBundle\Entity\Commentary;
use UserBundle\Entity\User;

/**
 * Class CommentaryRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryRepositoryTest extends WebTestCase
{
    /**
     * Set the commentary entity in BDD.
     */
    public function setUp()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setName('Loulier');
        $author->setFirstName('Guillaume');
        $author->setUsername('Guikingone');
        $author->setRoles('ROLE_ADMIN');

        // Create a tricks to link the commentary to this specific tricks.
        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate('26-12-2016');
        $tricks->setAuthor($author);
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple test.');

        $commentary = new Commentary();
        $commentary->setCreationDate('26-12-2016');
        $commentary->setAuthor($author);
        $commentary->setTricks($tricks);
        $commentary->setContent('A simple commentary');

        $kernel = self::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $doctrine->persist($commentary);
        $doctrine->flush();
    }

    /**
     * Test if all the commentaries are found.
     */
    public function testAllCommentaryIsFound()
    {
        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findAll();

        if (is_array($commentary)) {
            $this->assertNotNull($commentary);
        }
    }

    /**
     * Test if the commentary can be found using his id.
     */
    public function testCommentaryIsFoundById()
    {
        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findOneBy(['id' => 0]);

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
     * Test if the commentaries can be found using tricks id.
     */
    public function testCommentaryIsFoundByTricksId()
    {
        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findBy(['tricks' => 0]);

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
        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findOneBy(['tricks' => 0]);

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
        $kernel = static::createKernel();
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findAll();

        foreach ($commentary as $cmt) {
            $doctrine->remove($cmt);
        }

        // try to find the entities after remove.
        $commentaries = $doctrine->getRepository('AppBundle:Commentary')->findAll();

        $this->assertEmpty($commentaries);
    }
}
