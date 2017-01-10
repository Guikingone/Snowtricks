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

use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Yaml\Yaml;

// Entity
use AppBundle\Entity\Tricks;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class FileManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class FileManager
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var Workflow
     */
    private $workflow;

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
     * @param Workflow      $workflow
     */
    public function __construct(
        $rootDir,
        EntityManager $doctrine,
        Workflow $workflow
    ) {
        $this->rootDir = $rootDir;
        $this->doctrine = $doctrine;
        $this->workflow = $workflow;
    }

    /**
     * Allow to build the cache for files used and store the cache
     * inside the class, every key is passed into a Entity and store into
     * an array and the BDD.
     *
     * @throws \InvalidArgumentException
     * @throws ParseException
     * @throws \LogicException
     * @throws LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function loadTricksWithCache()
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
                    // Return the file using the cache path.
                            $this->cache->getPath()
                    )
                );
                $author = $this->doctrine->getRepository('UserBundle:User')
                                         ->findOneBy([
                                             'roles' => 'ROLE_ADMIN'
                                         ]);
                // Instantiate the entity in order to save memory into the loop.
                $trick = new Tricks();
                foreach ($values as $value => $item) {
                    // Clone the entity to respect the loop.
                    $tricks = clone $trick;
                    $tricks->setName($value);
                    $tricks->setCreationDate(new \DateTime());
                    $tricks->setGroups($item['groups']);
                    $tricks->setResume($item['resume']);
                    $tricks->setAuthor($author);
                    $tricks->setValidated(true);
                    $tricks->setPublished(true);

                    $this->workflow->apply($tricks, 'start_phase');
                    $this->workflow->apply($tricks, 'validation_phase');

                    // Store in array for future check.
                    $this->tricks[$tricks->getName()] = $tricks;
                }

                foreach ($this->tricks as $trick) {
                    $this->doctrine->persist($trick);
                }
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

        // Flush the entire array of entities.
        $this->doctrine->flush();
    }

    /**
     * Allow to find the files used for hydrating the BDD before production,
     * the file is parsed with Yaml component and every entity
     * are stored into the class for future check.
     *
     * @throws \InvalidArgumentException
     * @throws ParseException
     * @throws LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function loadTricksWithoutCache()
    {
        // Store the files path into the class.
        $this->paths = $this->rootDir.'/files';
        $finder = new Finder();
        $finder->files()->in($this->paths);

        // Instantiate the entity in order to save memory into the loop.
        $trick = new Tricks();
        foreach ($finder as $file) {
            $value = Yaml::parse(
                file_get_contents(
                    $file
                )
            );

            // Find a user who's admin to link the tricks.
            $author = $this->doctrine->getRepository('UserBundle:User')
                                     ->findOneBy([
                                         'roles' => 'ROLE_ADMIN'
                                     ]);

            foreach ($value as $values => $item) {
                // Clone the entity to respect the loop.
                $tricks = clone $trick;
                $tricks->setName($values);
                $tricks->setCreationDate(new \DateTime());
                $tricks->setGroups($item['groups']);
                $tricks->setResume($item['resume']);
                $tricks->setAuthor($author);
                $tricks->setValidated(true);
                $tricks->setPublished(true);

                $this->workflow->apply($tricks, 'start_phase');
                $this->workflow->apply($tricks, 'validation_phase');

                // Store in array for future check.
                $this->tricks[$tricks->getName()] = $tricks;
            }

            foreach ($this->tricks as $trick) {
                $this->doctrine->persist($trick);
            }
        }

        // Flush the entity array outside of the loop.
        $this->doctrine->flush();
    }
}
