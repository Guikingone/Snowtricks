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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TricksCommandTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksCommandTest extends KernelTestCase
{
    /**
     * Test if the different tricks are hydrated.
     */
    public function testTricksAreHydratedWithoutCache()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $application = new Application($kernel);

        $command = $application->find('appbundle:tricks:hydrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('finished', $output);
    }
}
