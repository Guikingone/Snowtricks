<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

// Entity
use UserBundle\Entity\User;

// Forms
use UserBundle\Form\Type\RegisterType;

/**
 * Class Security.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr
 */
class Security
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var FormFactory
     */
    private $form;

    /**
     * @var AuthorizationChecker
     */
    private $security;

    /**
     * @var UserPasswordEncoder
     */
    private $password;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var TraceableEventDispatcher
     */
    private $dispatcher;

    /**
     * Security constructor.
     *
     * @param EntityManager $doctrine
     * @param FormFactory $form
     * @param AuthorizationChecker $security
     * @param UserPasswordEncoder   $password
     * @param TokenStorage $tokenStorage
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        AuthorizationChecker $security,
        UserPasswordEncoder $password,
        TokenStorage $tokenStorage,
        TraceableEventDispatcher $dispatcher
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->security = $security;
        $this->password = $password;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Allow to register a new user.
     *
     * @param Request $request
     *
     * @throws InvalidOptionsException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\Form\FormView|RedirectResponse
     */
    public function registerUser(Request $request)
    {
        $user = new User();

        $form = $this->form->create(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->password->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $this->doctrine->persist($user);
            $this->doctrine->flush();

            return new RedirectResponse('home');
        }

        return $form->createView();
    }
}
