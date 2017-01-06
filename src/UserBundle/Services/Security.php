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
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Entity
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;

// Forms
use UserBundle\Events\ConfirmedUserEvent;
use UserBundle\Form\ForgotPasswordType;
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
     * @param UserPasswordEncoder      $password
     * @param TokenStorage             $tokenStorage
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
        UserPasswordEncoder $password,
        TokenStorage $tokenStorage,
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
        $this->password = $password;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->workflow = $workflow;
        $this->templating = $templating;
        $this->mailer = $mailer;
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

    /**
     * Allow to reinitialize the password if the user have lost him.
     *
     * @param Request $request
     *
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
