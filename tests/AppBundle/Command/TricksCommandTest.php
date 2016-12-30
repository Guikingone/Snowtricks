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

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;

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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        // Instantiate Doctrine for BDD queries after command.
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

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
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'appbundle:tricks:hydrate',
        ]);

        $application->run($input);

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
