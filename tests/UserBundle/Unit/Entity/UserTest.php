<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Unit\Entity;

use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

// Entity
use UserBundle\Entity\User;

/**
 * Class UserTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserTest extends TestCase
{
    /**
     * Test the boot of the Entity.
     */
    public function testUserHydratation()
    {
        $user = new User();
        $user->setFirstname('Arnaud');
        $user->setLastname('Tricks');
        $user->setBirthdate(new \DateTime());
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setEmail('contact@snowtricks.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setActive(true);

        $this->assertNull($user->getId());
        $this->assertEquals('Arnaud', $user->getFirstname());
        $this->assertEquals('Tricks', $user->getLastname());
        $this->assertEquals(new \DateTime(), $user->getBirthdate());
        $this->assertEquals('Professional snowboarder', $user->getOccupation());
        $this->assertEquals('Nono', $user->getUsername());
        $this->assertEquals('Lk__DTHE', $user->getPassword());
        $this->assertEquals('contact@snowtricks.fr', $user->getEmail());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertEquals('dd21498e61e26a5a42d3g9r4z2a364f2s3a2', $user->getToken());
        $this->assertTrue($user->getValidated());
        $this->assertFalse($user->getLocked());
        $this->assertTrue($user->getActive());
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isCredentialsNonExpired());
    }

    /**
     * Test the relation between User and Commentary.
     */
    public function testUserCommentaryLink()
    {
        $user = new User();
        $user->setFirstname('Arnaud');
        $user->setLastname('Tricks');
        $user->setBirthdate(new \DateTime());
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setEmail('contact@snowtricks.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setActive(true);

        $commentary = new Commentary();
        $commentary->setAuthor($user);
        $commentary->setPublicationDate(new \DateTime());
        $commentary->setContent('A simple commentary');

        $user->addCommentary($commentary);

        $this->assertContains($commentary, $user->getCommentary());

        $user->removeCommentary($commentary);

        $this->assertNotContains($commentary, $user->getCommentary());
    }

    /**
     * Test the relation between User and Tricks.
     */
    public function testUserTricksLink()
    {
        $user = new User();
        $user->setFirstname('Arnaud');
        $user->setLastname('Tricks');
        $user->setBirthdate(new \DateTime());
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setEmail('contact@snowtricks.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setActive(true);

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setAuthor($user);
        $tricks->setCreationDate(new \DateTime());
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple test.');

        $user->addTrick($tricks);

        $this->assertContains($tricks, $user->getTricks());

        $user->removeTrick($tricks);

        $this->assertNotContains($tricks, $user->getTricks());
    }
}
