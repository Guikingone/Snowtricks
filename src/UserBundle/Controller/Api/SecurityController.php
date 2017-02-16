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
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class SecurityController.
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

    /**
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws UsernameNotFoundException
     * @throws \InvalidArgumentException
     * @throws JWTEncodeFailureException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function loginAction()
    {
        return $this->get('api.security')->login();
    }

    /**
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws \LogicException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function forgotPasswordAction()
    {
        return $this->get('api.security')->forgotPassword();
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function validatedProfileAction()
    {
        return $this->get('api.security')->validateUser();
    }
}
