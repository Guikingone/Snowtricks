<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\User;

/**
 * Class LoadUserData.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Arnaud');
        $author->setLastname('Duchemin');
        $author->setUsername('Duduche');
        $author->setRoles(['ROLE_ADMIN']);
        $author->setBirthdate(new \DateTime());
        $author->setOccupation('Rally Driver');
        $author->setEmail('guik@guillaumeloulier.fr');
        $author->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $author->setValidated(true);
        $author->setLocked(false);
        $author->setActive(true);

        // Create a user in order to simulate the authentication process.
        $author_II = new User();
        $author_II->setLastname('Loulier');
        $author_II->setFirstname('Guillaume');
        $author_II->setUsername('Guikingone');
        $author_II->setBirthdate(new \DateTime());
        $author_II->setRoles(['ROLE_ADMIN']);
        $author_II->setOccupation('Rally Driver');
        $author_II->setEmail('guik@guillaumeloulier.fr');
        $author_II->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $author_II->setValidated(true);
        $author_II->setLocked(false);
        $author_II->setActive(true);

        $manager->persist($author);
        $manager->persist($author_II);
        $manager->flush();

        $this->addReference('author', $author);
        $this->setReference('author_II', $author_II);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
