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
use AppBundle\Entity\Commentary;
use UserBundle\Entity\User;

/**
 * Class CommentaryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryTest extends TestCase
{
    /**
     * Test the boot of the entity.
     */
    public function testCommentaryBoot()
    {
        $commentary = new Commentary();
        $commentary->setPublicationDate('26-12-2016');
        $commentary->setContent('A simple test');

        $this->assertEquals('26-12-2016', $commentary->getPublicationDate());
        $this->assertEquals('A simple test', $commentary->getContent());
    }

    /**
     * Test the hydratation and relations of the entity.
     */
    public function testCommentaryHydratation()
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
        $tricks->setCreationDate('26/12/2016');
        $tricks->setAuthor($author);
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple test.');

        $commentary = new Commentary();
        $commentary->setAuthor($author);
        $commentary->setDate('26/12/2016');
        $commentary->setTricks($tricks);
        $commentary->setContent('A simple commentary');

        $this->assertEquals('26/12/2016', $commentary->getDate());
        $this->assertEquals('A simple commentary', $commentary->getContent());

        // Test the relation between entity in order to validate the typehint.
        $this->assertEquals($author, $commentary->getAuthor());
        $this->assertArrayHasKey($tricks, $commentary->getTricks());
    }
}
