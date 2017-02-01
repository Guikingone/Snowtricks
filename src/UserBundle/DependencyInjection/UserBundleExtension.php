<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

// Managers
use UserBundle\Managers\UserManager;

// Services
use UserBundle\Services\Security;

/**
 * Class UserBundleExtension.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserBundleExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->addClassesToCompile([
            UserManager::class,
            Security::class,
        ]);
    }
}
