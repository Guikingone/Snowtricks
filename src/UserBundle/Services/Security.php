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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

// Entity
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use UserBundle\Entity\User;

// Forms
use UserBundle\Events\ConfirmedUserEvent;
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
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

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
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param AuthorizationChecker     $security
     * @param AuthenticationUtils      $authenticationUtils
     * @param UserPasswordEncoder      $password
     * @param TokenStorage             $tokenStorage
     * @param TraceableEventDispatcher $dispatcher
     * @param RequestStack             $requestStack
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        AuthorizationChecker $security,
        AuthenticationUtils $authenticationUtils,
        UserPasswordEncoder $password,
        TokenStorage $tokenStorage,
        TraceableEventDispatcher $dispatcher,
        RequestStack $requestStack
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->security = $security;
        $this->authenticationUtils = $authenticationUtils;
        $this->password = $password;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * Allow to validate a user using the generated token.
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return RedirectResponse
     */
    public function validateUser()
    {
        $token = $this->requestStack->getCurrentRequest()->get('token');

        if (!is_int($token)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The token MUST be a integer !, 
                    given "%s"', gettype($token)
                )
            );
        }

        $user = $this->doctrine->getRepository('UserBundle:User')
                               ->findOneBy(['token' => $token]);

        if (!$user) {
            throw new \LogicException(
                sprintf(
                    'The token isn\'t valid !'
                )
            );
        }

        if ($user->getToken() === $token) {
            $event = new ConfirmedUserEvent($user);
            $this->dispatcher->dispatch(ConfirmedUserEvent::NAME, $event);
        }

        return new RedirectResponse('login');
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
            $this->doctrine->persist($user);
            $this->doctrine->flush();

            return new RedirectResponse('home');
        }

        return $form->createView();
    }

    /**
     * Allow to log a single User.
     *
     * @return array
     */
    public function loginUser()
    {
        return [
            'errors' => $errors = $this->authenticationUtils->getLastAuthenticationError(),
            'lastUsername' => $lastUsername = $this->authenticationUtils->getLastUsername(),
        ];
    }
}
