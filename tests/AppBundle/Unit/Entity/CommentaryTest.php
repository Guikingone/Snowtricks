<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Unit\Entity;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

// Entity
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
        $commentary->setPublicationDate(new \DateTime());
        $commentary->setContent('A simple test');

        $this->assertEquals('A simple test', $commentary->getContent());
        $this->assertNull($commentary->getId());
    }

    /**
     * Test the hydratation and relations of the entity.
     */
    public function testCommentaryHydratation()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Guillaume');
        $author->setLastname('Loulier');
        $author->setUsername('Guikingone');
        $author->setRoles(['ROLE_ADMIN']);

        // Create a tricks to link the commentary to this specific tricks.
        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate(new \DateTime());
        $tricks->setAuthor($author);
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple test.');

        $commentary = new Commentary();
        $commentary->setAuthor($author);
        $commentary->setPublicationDate(new \DateTime());
        $commentary->setTricks($tricks);
        $commentary->setContent('A simple commentary');

        $author->addCommentary($commentary);

        // Test the relation between entity in order to validate the typehint.
        $this->assertEquals('A simple commentary', $commentary->getContent());
        $this->assertEquals($author, $commentary->getAuthor());
        $this->assertEquals($tricks, $commentary->getTricks());
        $this->assertContains($commentary, $author->getCommentary());

        $author->removeCommentary($commentary);

        $this->assertNotContains($commentary, $author->getCommentary());
    }
}
