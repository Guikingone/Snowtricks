<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class TricksCommandTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksCommandTest extends WebTestCase
{
    /**
     * Set the command into the application.
     */
    public function setUp()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'appbundle:tricks',
        ]);

        $application->run($input);
    }

    /**
     * Test if the different tricks are hydrated.
     */
    public function testTricksIsHydrated()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'appbundle:tricks',
        ]);

        $application->run($input);

        // Use Doctrine to find the tricks saved.
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tricks = $doctrine->getRepository('AppBundle:Tricks')->findAll();

        if (is_array($tricks)) {
            $this->assertArrayHasKey('Flip', $tricks->getGroups());
        }
    }
}
