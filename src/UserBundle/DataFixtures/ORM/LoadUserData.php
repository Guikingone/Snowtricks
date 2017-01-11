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

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

// Entity
use UserBundle\Entity\User;

/**
 * Class LoadUserData.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadUserData implements FixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setFirstname('Guillaume');
        $user->setLastname('Loulier');
        $user->setBirthdate(new \DateTime());
        $user->setEmail('contact@guillaumeloulier.fr');
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Guik');
        $user->setPassword('Lk__DTHE');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setActive(true);

        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
