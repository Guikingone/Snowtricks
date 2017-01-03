<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Tricks;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileManager
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class FileManager
{
    // Store the kernel.root.dir
    private $rootDir;

    /**
     * @var EntityManager
     */
    private $doctrine;

    // Store the cache.
    protected $cache;

    /**
     * @var array
     */
    private $tricks;

    /**
     * Manager constructor.
     *
     * @param             $rootDir
     * @param EntityManager     $doctrine
     */
    public function __construct($rootDir, EntityManager $doctrine)
    {
        $this->rootDir = $rootDir;
        $this->doctrine = $doctrine;
    }

    /**
     * Allow to build the cache for files used.
     *
     * @throws ParseException
     * @throws \LogicException
     * @throws ORMInvalidArgumentException
     */
    public function loadTricks()
    {
        $config = $this->rootDir . '/files';
        $this->cache = new ConfigCache($config, false);
        $locator = new FileLocator($config);

        if ($this->cache->isFresh()) {
            $file = $locator->locate('tricks.yml', null, false);
            if ($file) {
                // Grab the data's passed through the file.
                $values = Yaml::parse(
                    file_get_contents(
                        $this->rootDir . '/files/tricks.yml'
                    )
                );

                // Create a new Tricks to save the entries.
                $trick = new Tricks();
                foreach ($values['tricks'] as $value) {
                    $tricks = clone $trick;
                    $tricks->setName($value['tricks']['name']);
                    $tricks->setCreationDate(new \DateTime());
                    $tricks->setGroups($value['tricks']['groups']);
                    $tricks->setResume($value['tricks']['resume']);
                    $tricks->setValidated(true);
                    $tricks->setPublished(true);

                    // Store the result in order to persist.
                    $this->tricks[$tricks->getName()][$tricks];
                }

                foreach ($this->tricks as $trick) {
                    if (!$trick instanceof Tricks) {
                        throw new \LogicException(
                            sprintf(
                                'The entity MUST be a instance of Tricks !
                                Given "%s"', get_class($trick)
                            )
                        );
                    }

                    $this->doctrine->persist($trick);
                }
            }
        }
    }

    /**
     * Check if the cache is fresh, in other case,
     * the listener linked to the Event check the file and update the BDD.
     */
    public function checkCache()
    {
        if (!$this->cache->isFresh()) {

        }
    }
}