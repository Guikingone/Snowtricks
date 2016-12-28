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

/**
 * Class TricksRepositoryTest
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
        $tricks = new Tricks();
        $tricks->setName('Backflip');
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
     * Test if the Tricks can be find by his name.
     */
    public function testTricksFindByName()
    {
        $client = self::createKernel();

        $tricks = $client->getContainer()->get('doctrine.orm.entity_manager')
                         ->getRepository('AppBundle:Repository:TricksRepository')
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
        $client = self::createKernel();

        $tricks = $client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Repository:TricksRepository')
            ->findBy(array('group' => 'Flip'));

        if (is_array($tricks)) {
            foreach ($tricks as $trick) {
                $this->assertArrayHasKey('Flip', $trick->getGroup());
            }
        }
    }

    /**
     * Test if a Trick can be found by his name and remove.
     */
    public function testTricksSuppression()
    {
        $client = self::createKernel();

        $tricks = $client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Repository:TricksRepository')
            ->findOneBy(array('name' => 'Backflip'));

        $client->getContainer()->get('doctrine.orm.entity_manager')->remove($tricks);
    }
}