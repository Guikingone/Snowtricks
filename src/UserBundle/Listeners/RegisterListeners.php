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

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use UserBundle\Entity\User;
use UserBundle\Events\ConfirmedUserEvent;

/**
 * Class RegisterListeners.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class RegisterListeners
{
    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * RegisterListeners constructor.
     *
     * @param UserPasswordEncoder $encoder
     * @param Session             $session
     * @param TwigEngine          $templating
     */
    public function __construct(
        UserPasswordEncoder $encoder,
        Session $session,
        TwigEngine $templating
    ) {
        $this->encoder = $encoder;
        $this->session = $session;
        $this->templating = $templating;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \LogicException
     */
    public function onPrePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            throw new \LogicException(
                sprintf(
                    'The entity MUST be a instance of User, 
                    given "%s"', get_class($entity)
                )
            );
        }

        $password = $this->encoder->encodePassword($entity, $entity->getPassword());
        $entity->setPassword($password);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onPostPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            throw new \LogicException(
                sprintf(
                    'The entity MUST be a instance of User, 
                    given "%s"', get_class($entity)
                )
            );
        }

        $token = random_int(0, 2942954362);
        $entity->setToken($token);
        $entity->setValidated(false);

        $this->session->getFlashBag()->add(
            'success',
            'Votre profil a bien été enregistré, 
            un email de confirmation vous sera envoyé.'
        );

        $mail = \Swift_Message::newInstance()
            ->setSubject('Snowtricks - Notification system')
            ->setFrom('contact@snowtricks.fr')
            ->setTo($entity->getAuthor()->getEmail())
            ->setBody($this->templating->render(
                ':Mails:Users:notif_profil_creation.html.twig', [
                    'user' => $entity,
                ]
            ), 'text/html');

        $this->mailer->send($mail);
    }

    /**
     * @param ConfirmedUserEvent $event
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onValidatedUser(ConfirmedUserEvent $event)
    {
        $user = $event->getUser();

        $user->setValidated(true);

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
                ':Mails:Users:notif_profil_validation.html.twig', [
                    'user' => $user,
                ]
            ), 'text/html');

        $this->mailer->send($mail);
    }
}
