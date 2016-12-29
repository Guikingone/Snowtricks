<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TchatRepositoryTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TchatRepositoryTest extends WebTestCase
{
    /**
     * Test if the commentary are found.
     */
    public function testCommentaryIsFound()
    {
        $kernel = static::createKernel();

        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $commentary = $doctrine->getRepository('AppBundle:Commentary')->findAll();

        if (is_array($commentary)) {
            $this->assertNull($commentary);
        }
    }
}
