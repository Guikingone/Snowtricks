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
use AppBundle\Entity\Tricks;
use UserBundle\Entity\User;

/**
 * Class TricksTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksTest extends TestCase
{
    /**
     * Test the hydratation of the Entity.
     */
    public function testEntityBoot()
    {
        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate('26/12/2016');
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple backflip content ...');

        $this->assertEquals('Backflip', $tricks->getName());
        $this->assertEquals('26/12/2016', $tricks->getCreationDate());
        $this->assertArrayHasKey('Flip', $tricks->getGroups());
        $this->assertEquals('A simple backflip content ...', $tricks->getResume());
    }

    /**
     * Test if a Author can be added to a Tricks.
     */
    public function testAuthorEntityHydratation()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstName('Arnaud');
        $author->setLastName('Duchemin');
        $author->setUsername('Duduche');
        $author->setRoles('ROLE_ADMIN');

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate('26/12/2016');
        $tricks->setAuthor($author);
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple test.');

        // Keep the same tests in order to validate the new author.
        $this->assertEquals('Backflip', $tricks->getName());
        $this->assertEquals('26/12/2016', $tricks->getCreationDate());
        $this->assertEquals($author->getName(), $tricks->getAuthor());
        $this->assertArrayHasKey('Flip', $tricks->getGroups());
        $this->assertEquals('A simple test.', $tricks->getResume());
    }
}
