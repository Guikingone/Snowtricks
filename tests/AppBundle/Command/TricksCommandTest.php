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
use Doctrine\ORM\EntityManager;

/**
 * Class TricksCommandTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksCommandTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * Test if the different tricks are hydrated.
     */
    public function testTricksIsHydrated()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        // Instantiate Doctrine for BDD queries after command.
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $command = $application->find('appbundle:tricks:hydrate');
        if ($command) {
            $commandTester = new CommandTester($command);
            $commandTester->execute([
                'command' => $command->getName()
            ]);
        }

        $commandTester->getDisplay();

        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')->findAll();

        if (is_array($tricks)) {
            $this->assertArrayHasKey('Flip', $tricks->getGroups());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->close();
        $this->doctrine = null;
    }
}
