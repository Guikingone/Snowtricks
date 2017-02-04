<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    private $doctrine;
    private $manager;
    private $schemaTools;
    private $classes;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $doctrine
     */
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTools = new \Doctrine\ORM\Tools\SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @BeforeScenario
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function createSchema()
    {
        $this->schemaTools->dropSchema($this->classes);
        $this->schemaTools->createSchema($this->classes);
    }
}
