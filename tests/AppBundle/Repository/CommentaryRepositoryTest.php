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
use AppBundle\Entity\Commentary;

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
        $tricks = new Commentary();
        $tricks->setAuthor('Backflip');
        $tricks->setCreationDate('26/12/2016');
        $tricks->setAuthor('Guik');
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
     * Test if the commentary are found.
     */
    public function testCommentaryIsFound()
    {
        $kernel = static::createKernel();

        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findAll();

        if (is_array($commentary)) {
            $this->assertNull($commentary);
        }
    }

    public function testCommentaryIsFoundById()
    {
    }
}
