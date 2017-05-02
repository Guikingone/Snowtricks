<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class LoadTricksCommand.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadTricksCommand extends ContainerAwareCommand
{
    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('appbundle:tricks:hydrate')
            ->setDescription('Load the tricks')
            ->setHelp('Allow to load the tricks in BDD by parsing tricks.yml file')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws ParseException
     * @throws ORMInvalidArgumentException
     * @throws LogicException
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output
            ->writeln([
                '',
                '<info>This command gonna load the tricks stored into the tricks.yml file and hydrate the BDD.</info>',
                '=========================================================================================',
                '',
            ]);
        $progress = new ProgressBar($output, 40);
        $progress->setFormat('verbose');
        $progress->start();
        $progress->advance(20);
        // Cal the FileManager service without the cache.
        $this->getContainer()->get('app.manager')->loadTricksWithoutCache();
        $progress->advance(20);
        $progress->finish();
        $output
            ->writeln([
                '',
                '',
                '<info>Hydratation finished, let\'s get to work !.</info>',
                '========================================================',
                '',
            ]);
    }
}
