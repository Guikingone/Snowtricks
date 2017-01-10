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

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadTricksData
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadTricksData implements FixtureInterface
{
    public function load (ObjectManager $manager)
    {
        // TODO: Implement load() method.
    }
}