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
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Entity
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;

// Events
use UserBundle\Events\ForgotPasswordEvent;
use UserBundle\Events\UserRegisteredEvent;

// Forms
use UserBundle\Form\Type\ForgotPasswordType;
use UserBundle\Form\Type\RegisterType;

// Exceptions
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use UserBundle\Listeners\RegisterListeners;

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
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var Workflow
     */
    private $workflow;

    /** @var TraceableEventDispatcher */
    private $dispatcher;

    /**
     * Security constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param AuthenticationUtils      $authenticationUtils
     * @param Workflow                 $workflow
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        AuthenticationUtils $authenticationUtils,
        Workflow $workflow,
        TraceableEventDispatcher $dispatcher
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->authenticationUtils = $authenticationUtils;
        $this->workflow = $workflow;
        $this->dispatcher = $dispatcher;
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
     * @see UserRegisteredEvent
     * @see RegisterListeners::onUserRegistered()
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
            // Send the event who's gonna set the password
            // and send the token to the user.
            $event = new UserRegisteredEvent($user);
            $this->dispatcher->dispatch(UserRegisteredEvent::NAME, $event);

            $this->doctrine->persist($user);
            $this->doctrine->flush();

            $response = new RedirectResponse('home');
            $response->send();
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
     * @param Request $request
     *
     * @throws InvalidOptionsException
     * @throws \LogicException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @see ForgotPasswordType
     * @see ForgotPasswordEvent
     * @see RegisterListeners::onForgotPassword()
     *
     * @return FormView
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

            $event = new ForgotPasswordEvent($user);
            $this->dispatcher->dispatch(ForgotPasswordEvent::NAME, $event);

            $this->doctrine->flush();
        }

        return $form->createView();
    }
}
