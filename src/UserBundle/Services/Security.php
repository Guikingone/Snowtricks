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
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Entity
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;

// Forms
use UserBundle\Events\ConfirmedUserEvent;
use UserBundle\Form\ForgotPasswordType;
use UserBundle\Form\Type\RegisterType;

// Exceptions
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;

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
     * @var Session
     */
    private $session;

    /**
     * @var AuthorizationChecker
     */
    private $security;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var TraceableEventDispatcher
     */
    private $dispatcher;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Security constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param Session                  $session
     * @param AuthorizationChecker     $security
     * @param AuthenticationUtils      $authenticationUtils
     * @param TraceableEventDispatcher $dispatcher
     * @param RequestStack             $requestStack
     * @param Workflow                 $workflow
     * @param TwigEngine               $templating
     * @param \Swift_Mailer            $mailer
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        Session $session,
        AuthorizationChecker $security,
        AuthenticationUtils $authenticationUtils,
        TraceableEventDispatcher $dispatcher,
        RequestStack $requestStack,
        Workflow $workflow,
        TwigEngine $templating,
        \Swift_Mailer $mailer
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->session = $session;
        $this->security = $security;
        $this->authenticationUtils = $authenticationUtils;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->workflow = $workflow;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    /**
     * Allow to return all the users.
     *
     * @return array|User[]
     */
    public function getUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findAll();
    }

    /**
     * Return a single user using his firstname.
     *
     * @param string $name
     *
     * @return null|User
     */
    public function getUser(string $name)
    {
        return $this->doctrine->getRepository('UserBundle:User')->findOneBy(['lastname' => $name]);
    }

    /**
     * Return every users not validated.
     *
     * @return array|User[]
     */
    public function getUsersNotValidated()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['validated' => false]);
    }

    /**
     * Return every users validated.
     *
     * @return array|User[]
     */
    public function getUsersValidated()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['validated' => true]);
    }

    /**
     * Return all the users locked.
     *
     * @return array|User[]
     */
    public function getLockedUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['locked' => true]);
    }

    /**
     * Return all the users unlocked.
     *
     * @return array|User[]
     */
    public function getUnlockedUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['locked' => false]);
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
                               ->findOneBy([
                                   'token' => $token,
                               ]);

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
     * Allow to lock a user using his lastname.
     *
     * @param string $name
     *
     * @throws AccessDeniedException
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function lockUser(string $name)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException(
                sprintf(
                    'L\'accès à cette ressource est bloqué 
                    aux administrateurs !'
                )
            );
        }

        $user = $this->doctrine->getRepository('UserBundle:User')
                               ->findOneBy([
                                   'lastname' => $name,
                               ]);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The entity received MUST be a instance of User !,
                     given "%s"', get_class($user)
                )
            );
        }

        $user->setLocked(true);
        $user->setActive(false);

        $this->doctrine->flush();
        $this->session->getFlashBag()->add(
            'success',
            'L\'utilisateur a bien été bloqué.'
        );

        return new RedirectResponse('admin');
    }

    /**
     * Allow to unlock a user using his lastname and the boolean
     * of his lock phase.
     *
     * @param string $name
     *
     * @throws AccessDeniedException
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function unlockUser(string $name)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException(
                sprintf(
                    'L\'accès à cette ressource est bloqué 
                    aux administrateurs !'
                )
            );
        }

        $user = $this->doctrine->getRepository('UserBundle:User')
                               ->findOneBy([
                                   'lastname' => $name,
                                   'locked' => true,
                               ]);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The entity received MUST be a instance of User !,
                     given "%s"', get_class($user)
                )
            );
        }

        $user->setLocked(false);
        $user->setActive(true);

        $this->doctrine->flush();
        $this->session->getFlashBag()->add(
            'success',
            'L\'utilisateur a bien été débloqué.'
        );

        return new RedirectResponse('admin');
    }

    /**
     * Allow to register a new user.
     *
     * @param Request $request
     *
     * @throws LogicException
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

        // Init the workflow
        $this->workflow->apply($user, 'register_phase');

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

    /**
     * Allow to reinitialize the password if the user have lost him.
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     * @throws InvalidOptionsException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws OptimisticLockException
     * @throws \RuntimeException
     * @throws \Twig_Error
     *
     * @return RedirectResponse
     */
    public function forgotPassword(Request $request)
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException(
                sprintf(
                    'L\'accès à cette ressource est bloqué 
                    aux utilisateurs de la plateforme !'
                )
            );
        }

        $form = $this->form->create(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = $this->doctrine->getRepository('UserBundle:User')
                                   ->findOneBy([
                                       'username' => $data['username'],
                                   ]);

            if (!$user) {
                throw new \LogicException(
                    sprintf(
                        'The user does not exist into the BDD, 
                    be sure to have to have submit the right username !'
                    )
                );
            }

            // Generate a alternative password.
            $password = uniqid('password_', true);
            $user->setPassword($password);

            $this->doctrine->flush();

            $this->session->getFlashBag()->add(
                'success',
                'Votre mot de passe a été réinitialisé, 
            vous le recevrez par mail, 
            merci de le changer après votre prochaine connexion.'
            );

            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($user->getEmail())
                ->setBody($this->templating->render(
                    ':Mails/Users:notif_password_forgot.html.twig', [
                        'user' => $user,
                    ]
                ), 'text/html');

            $this->mailer->send($mail);
        }

        return new RedirectResponse('home');
    }
}
