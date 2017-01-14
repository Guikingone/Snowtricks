<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Functionnal\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use UserBundle\Entity\User;

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

    /** {@inheritdoc} */
    public function setUp()
    {
        // Instantiate the service needed.
        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Arnaud');
        $author->setLastname('Duchemin');
        $author->setLastname('Duduche');
        $author->setUsername('Nono');
        $author->setRoles(['ROLE_ADMIN']);
        $author->setBirthdate(new \DateTime());
        $author->setOccupation('Rally Driver');
        $author->setEmail('guik@guillaumeloulier.fr');
        $author->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $author->setValidated(true);
        $author->setLocked(false);
        $author->setActive(true);

        $this->doctrine->persist($author);
        $this->doctrine->flush();
    }

    /**
     * Test if the different tricks are hydrated.
     */
    public function testTricksAreHydratedWithCache()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $application = new Application($kernel);

        $command = $application->find('appbundle:tricks:hydrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'version' => 'cache',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('cache', $output);
    }

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
            'version' => 'nocache',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('nocache', $output);
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->clear(User::class);
        $this->doctrine->close();
        $this->doctrine = null;
    }
}
