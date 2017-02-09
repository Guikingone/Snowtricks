<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class SecurityController
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityController extends Controller
{
    /**
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerAction()
    {
        return $this->get('api.security')->register();
    }

    public function loginAction()
    {
        return $this->get('api.security')->login();
    }
}