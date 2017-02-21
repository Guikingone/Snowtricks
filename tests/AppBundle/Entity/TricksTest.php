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

// Entity
use AppBundle\Entity\Tricks;
use AppBundle\Entity\Commentary;
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
        $tricks->setCreationDate(new \DateTime());
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple backflip content ...');
        $tricks->setValidated(true);

        // Media management.
        $tricks->addVideos('nzT8sZE98Js');
        $tricks->setVideos([
            'nzT8sZE98Js',
            'nzT8sZdj87s',
            'nzT8sZE98Js',
        ]);
        $tricks->setImages([
           'coucoun.png',
           'Paris.png',
           'Val d\isère.jpeg',
        ]);
        $tricks->addImage('coucou.png');

        $this->assertEquals('Backflip', $tricks->getName());
        $this->assertEquals(new \DateTime(), $tricks->getCreationDate());
        $this->assertEquals('Flip', $tricks->getGroups());
        $this->assertEquals('A simple backflip content ...', $tricks->getResume());
        $this->assertContains('nzT8sZE98Js', $tricks->getVideos());
        $this->assertContains('nzT8sZE98Js', $tricks->getVideos());
        $this->assertContains('Paris.png', $tricks->getImages());
        $this->assertContains('coucou.png', $tricks->getImages());
        $this->assertEquals('coucou.png', $tricks->getImage('coucou.png'));
        $this->assertTrue($tricks->getValidated());
    }

    /**
     * Test if a Author can be added to a Tricks.
     */
    public function testAuthorEntityHydratation()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Arnaud');
        $author->setLastname('Duchemin');
        $author->setUsername('Duduche');
        $author->setRoles(['ROLE_ADMIN']);

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate(new \DateTime());
        $tricks->setAuthor($author);
        $tricks->setGroups(['Flip' => 'Flip']);
        $tricks->setResume('A simple test.');

        // Keep the same tests in order to validate the new author.
        $this->assertNull($tricks->getId());
        $this->assertEquals('Backflip', $tricks->getName());
        $this->assertEquals(new \DateTime(), $tricks->getCreationDate());
        $this->assertEquals($author, $tricks->getAuthor());
        $this->assertContains('Flip', $tricks->getGroups());
        $this->assertEquals('A simple test.', $tricks->getResume());
        $this->assertTrue($tricks->isAuthor($author));
    }

    /**
     * Test if the relation works.
     */
    public function testTricksCommentaryRelation()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Arnaud');
        $author->setLastname('Duchemin');
        $author->setUsername('Duduche');
        $author->setRoles(['ROLE_ADMIN']);

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setCreationDate(new \DateTime());
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple test.');

        $commentary = new Commentary();
        $commentary->setPublicationDate(new \DateTime());
        $commentary->setTricks($tricks);
        $commentary->setContent('A simple commentary');

        // Link the entities.
        $author->addTrick($tricks);
        $tricks->addCommentary($commentary);

        $this->assertContains($commentary, $tricks->getCommentary());
        $this->assertContains($tricks, $author->getTricks());

        // Remove the entities.
        $author->removeTrick($tricks);
        $tricks->removeCommentary($commentary);

        $this->assertNotContains($commentary, $tricks->getCommentary());
        $this->assertNotContains($tricks, $author->getTricks());

        // Just for relation test.
        $this->assertEmpty($tricks->getImages());
    }
}
