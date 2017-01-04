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
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class FileManager
{
    // Store the kernel.root.dir
    private $rootDir;

    // Store the cache.
    protected $cache;

    // Store the path of files.
    private $paths;

    /**
     * @var array
     */
    private $tricks = [];

    /**
     * Manager constructor.
     *
     * @param               $rootDir
     * @param EntityManager $doctrine
     */
    public function __construct($rootDir, EntityManager $doctrine)
    {
        $this->rootDir = $rootDir;
        $this->doctrine = $doctrine;
    }

    /**
     * Allow to build the cache for files used.
     *
     * @throws \InvalidArgumentException
     * @throws ParseException
     * @throws \LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function loadTricks()
    {
        try {
            // Store the files path into the class.
            $this->paths = $this->rootDir.'/files';
            $locator = new FileLocator($this->paths);

            // Find the file and save him into the cache.
            $file = $locator->locate('tricks.yml', null, true);
            $this->cache = new ConfigCache($file, true);

            if ($file && $this->cache->isFresh()) {
                // Grab the data's passed through the file.
                $values = Yaml::parse(
                    file_get_contents(
                        $this->cache->getPath()
                    )
                );
                // Instantiate the entity in order to save memory into the loop.
                $trick = new Tricks();
                foreach ($values as $value => $item) {
                    // Clone the entity to respect the loop.
                    $tricks = clone $trick;
                    $tricks->setName($value);
                    $tricks->setCreationDate(new \DateTime());
                    $tricks->setGroups($item['groups']);
                    $tricks->setResume($item['resume']);
                    $tricks->setValidated(true);
                    $tricks->setPublished(true);

                    // Store in array for future check.
                    $this->tricks[$tricks->getName()] = $tricks;
                }

                foreach ($this->tricks as $trick) {
                    $this->doctrine->persist($trick);
                }

                $this->doctrine->flush();
            } else {
                throw new \LogicException(
                    sprintf(
                        'The cache MUST be fresh during the loading phase !'
                    )
                );
            }
        } catch (\InvalidArgumentException $exception) {
            $exception->getMessage();
        }
    }

    /**
     * Check if the cache is fresh, in other case,
     * the listener linked to the Event check the file and update the BDD.
     *
     * @throws \LogicException
     */
    public function checkCache()
    {

    }
}
