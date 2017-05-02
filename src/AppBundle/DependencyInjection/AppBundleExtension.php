<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

// Services
use AppBundle\Services\Uploader;

// Managers
use AppBundle\Managers\CommentaryManager;
use AppBundle\Managers\TricksManager;
use AppBundle\Services\FileManager;

/**
 * Class AppBundleExtension.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class AppBundleExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->addClassesToCompile([
            CommentaryManager::class,
            TricksManager::class,
            FileManager::class,
            Uploader::class,
        ]);
    }
}
