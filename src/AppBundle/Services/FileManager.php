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
use Symfony\Component\Console\Helper\ProgressBar;
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
     * Allow to build the cache for files used and store the cache
     * inside the class, every key is passed into a Entity and store into
     * an array and the BDD.
     *
     * @param ProgressBar $progressBar      To set the advancement of the task.
     *
     * @throws \InvalidArgumentException
     * @throws ParseException
     * @throws \LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function loadTricksWithCache(ProgressBar $progressBar)
    {
        // Only used for the progressbar.
        $i = 0;
        while ($i < 200) {
            try {
                $progressBar->advance(30);
                // Store the files path into the class.
                $this->paths = $this->rootDir . '/files';
                $locator = new FileLocator($this->paths);

                // Find the file and save him into the cache.
                $file = $locator->locate('tricks.yml', null, true);
                $this->cache = new ConfigCache($file, true);

                $progressBar->advance(70);
                if ($file && $this->cache->isFresh()) {
                    // Grab the data's passed through the file.
                    $values = Yaml::parse(
                        file_get_contents(
                        // Return the file using the cache path.
                            $this->cache->getPath()
                        )
                    );
                    // Instantiate the entity in order to save memory into the loop.
                    $trick = new Tricks();
                    $progressBar->advance(30);
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
                    $progressBar->advance(40);

                    foreach ($this->tricks as $trick) {
                        $this->doctrine->persist($trick);
                    }
                    $progressBar->advance(20);

                    $this->doctrine->flush();
                    $progressBar->finish();
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
    }

    /**
     * @param ProgressBar $progressBar
     */
    public function loadTricksWithoutCache(ProgressBar $progressBar)
    {

    }

    /**
     * Check if the cache is fresh, in other case,
     * the listener linked to the Event check the file and update the BDD.
     */
    public function checkCache()
    {

    }
}
