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

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->setHelp('Allow to load the tricks in BDD by parsing .yml file')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws ParseException
     * @throws ORMInvalidArgumentException
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
        $this->getContainer()->get('app.manager')->loadTricks();
    }
}
