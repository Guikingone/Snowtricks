<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Class TricksTest
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksTest extends TestCase
{
    /**
     * Test the hydratation of the Entity
     */
    public function testEntityBoot()
    {
        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate('26/12/2016');
        $tricks->setAuthor('Guik');
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple backflip content ...');

        $this->assertEquals('Backflip', $tricks->getName());
        $this->assertEquals('26/12/2016', $tricks->getCreationDate());
        $this->assertEquals('Guik', $tricks->getAuthor());
        $this->assertArrayHasKey('Flip', $tricks->getGroups());
        $this->assertEquals('A simple backflip content ...', $tricks->getResume());
    }
}