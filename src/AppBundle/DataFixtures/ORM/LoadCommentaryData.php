<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Commentary;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadCommentaryData.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadCommentaryData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $commentary = new Commentary();
        $commentary->setTricks($this->getReference('tricks'));
        $commentary->setAuthor($this->getReference('author'));
        $commentary->setContent('Hey !');
        $commentary->setPublicationDate(new \DateTime());

        $manager->persist($commentary);
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}
