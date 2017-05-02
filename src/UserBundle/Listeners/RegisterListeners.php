<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Listeners;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Workflow\Workflow;
use Symfony\Bundle\TwigBundle\TwigEngine;

// Entity
use UserBundle\Events\ForgotPasswordEvent;
use UserBundle\Events\UserRegisteredEvent;

// Manager
use UserBundle\Managers\UserManager;

// Service
use UserBundle\Services\Web\Security;

// Event
use UserBundle\Events\ConfirmedUserEvent;

// Exceptions
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class RegisterListeners.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class RegisterListeners
{
    /** @var UserPasswordEncoder */
    private $encoder;

    /** @var Session */
    private $session;

    /** @var Workflow */
    private $workflow;

    /** @var DefaultEncoder */
    private $jwtEncoder;

    /** @var TwigEngine */
    private $templating;

    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * RegisterListeners constructor.
     *
     * @param UserPasswordEncoder $encoder
     * @param Session             $session
     * @param Workflow            $workflow
     * @param DefaultEncoder      $jwtEncoder
     * @param TwigEngine          $templating
     * @param \Swift_Mailer       $mailer
     */
    public function __construct(
        UserPasswordEncoder $encoder,
        Session $session,
        Workflow $workflow,
        DefaultEncoder $jwtEncoder,
        TwigEngine $templating,
        \Swift_Mailer $mailer
    ) {
        $this->encoder = $encoder;
        $this->session = $session;
        $this->workflow = $workflow;
        $this->jwtEncoder = $jwtEncoder;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    /**
     * @param UserRegisteredEvent $event
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     *
     * @see Security::registerUser()
     */
    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $entity = $event->getUser();

        if (is_object($entity)) {
            $password = $this->encoder->encodePassword(
                $entity, $entity->getPlainPassword()
            );
            $entity->setPassword($password);
            $entity->setValidated(false);
            $entity->setLocked(false);
            $entity->setActive(false);

            $token = uniqid('token_', true);
            $entity->setToken($token);

            $this->session->getFlashBag()->add(
                'success',
                'Votre profil a bien été enregistré, 
            un email de confirmation vous sera envoyé.'
            );

            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($entity->getEmail())
                ->setBody($this->templating->render(
                    ':Mails/Users:notif_profil_creation.html.twig', [
                        'user' => $entity,
                    ]
                ), 'text/html');

            $this->mailer->send($mail);
        }
    }

    /**
     * @param ConfirmedUserEvent $event
     *
     * @throws LogicException
     * @throws JWTEncodeFailureException
     * @throws \RuntimeException
     * @throws \Twig_Error
     *
     * @see UserManager::validateUser()
     */
    public function onValidatedUser(ConfirmedUserEvent $event)
    {
        $user = $event->getUser();

        $user->setValidated(true);
        $user->setRoles(['ROLE_USER']);

        $token = $this->jwtEncoder->encode([
            'username' => $user->getUsername(),
        ]);

        $user->setApiKey($token);

        $this->workflow->apply($user, 'validation_phase');

        $this->session->getFlashBag()->add(
            'success',
            'Votre profil a bien été validé, 
            un email de confirmation vous sera envoyé,
            Vous pouvez désormais accéder à votre profil.'
        );

        $mail = \Swift_Message::newInstance()
            ->setSubject('Snowtricks - Notification system')
            ->setFrom('contact@snowtricks.fr')
            ->setTo($user->getEmail())
            ->setBody($this->templating->render(
                ':Mails/Users:notif_profil_validation.html.twig', [
                    'user' => $user,
                ]
            ), 'text/html');

        $this->mailer->send($mail);
    }

    /**
     * @param ForgotPasswordEvent $event
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     *
     * @see Security::forgotPassword()
     */
    public function onForgotPassword(ForgotPasswordEvent $event)
    {
        $entity = $event->getUser();

        if (is_object($entity)) {
            // Generate a alternative password.
            $password = $this->encoder->encodePassword(
                $entity,
                uniqid('password_', true)
            );
            $entity->setPassword($password);

            $this->session->getFlashBag()->add(
                'success',
                'Votre mot de passe a été réinitialisé, vous le recevrez par mail, 
            merci de le changer après votre prochaine connexion.'
            );

            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($entity->getEmail())
                ->setBody($this->templating->render(
                    ':Mails/Users:notif_password_forgot.html.twig', [
                        'user' => $entity,
                    ]
                ), 'text/html');

            $this->mailer->send($mail);
        }
    }
}
