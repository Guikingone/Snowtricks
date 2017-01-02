<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Listeners;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;

// Event
use AppBundle\Events\TricksRefusedEvent;
use AppBundle\Events\TricksValidatedEvent;

/**
 * Class TricksListeners.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksListeners
{
    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * @var AuthorizationChecker
     */
    private $security;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var Session
     */
    private $session;

    /**
     * TricksListeners constructor.
     *
     * @param Workflow             $workflow
     * @param AuthorizationChecker $security
     * @param TwigEngine           $templating
     * @param Session              $session
     * @param TokenStorage         $storage
     */
    public function __construct(
        Workflow $workflow,
        AuthorizationChecker $security,
        TwigEngine $templating,
        Session $session,
        TokenStorage $storage
    ) {
        $this->workflow = $workflow;
        $this->security = $security;
        $this->templating = $templating;
        $this->session = $session;
        $this->storage = $storage;
    }

    /**
     * if the user is granted as ADMIN, we set the validation, publication
     * and date by default.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \LogicException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Tricks || !$entity instanceof Commentary) {
            throw new \LogicException(
                sprintf(
                    'The entity submitted MUST be a instance of Tricks or Commentary, 
                    given "%s"', get_class($entity)
                )
            );
        }

        if ($entity instanceof Tricks && $this->security->isGranted('ROLE_ADMIN')) {
            $entity->setCreationDate(new \DateTime());
            $entity->setValidated(true);
            $entity->setPublished(true);
            // Set the workflow phase.
            $this->workflow->apply($entity, 'validation_phase');
        } elseif ($entity->getPublished() === false) {
            // In the case of entity update.
            $entity->setPublished(true);
        } else {
            // In the case of user submission.
            $entity->setValidated(false);
            $entity->setPublished(false);
        }

        if ($entity instanceof Commentary) {
            $entity->setAuthor($this->storage->getToken()->getUser());
        }
    }

    /**
     * Once the postPersist is launched, if the publication and validation setters
     * are defined and true, we send a email using the admin list.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \LogicException
     * @throws LogicException
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Tricks) {
            throw new \LogicException(
                sprintf(
                    'The entity submitted MUST be of type Tricks, 
                    given "%s"', get_class($entity)
                )
            );
        }

        if ($entity->getPublished() && $entity->getValidated() === true) {
            // Finalize the workflow.
            $this->workflow->apply($entity, 'publication_phase');

            $this->session->getFlashBag()->add(
                'success',
                'Le trick a bien été enregistré'
            );
            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($entity->getAuthor()->getEmail())
                ->setBody($this->templating->render(
                    ':Mails:notif_email.html.twig', [
                        'tricks' => $entity,
                    ]
                ), 'text/html');

            $this->mailer->send($mail);
        } else {
            $this->session->getFlashBag()->add(
                'success',
                'Le trick a été envoyé en validation.'
            );
        }
    }

    /**
     * @param TricksValidatedEvent $validatedEvent
     *
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onValidateTricks(TricksValidatedEvent $validatedEvent)
    {
        $mail = \Swift_Message::newInstance()
            ->setSubject('Snowtricks - Notification system')
            ->setFrom('contact@snowtricks.fr')
            ->setTo($validatedEvent->getTricks()->getAuthor()->getEmail())
            ->setBody($this->templating->render(
                ':Mails:validate_tricks.html.twig', [
                    'tricks' => $validatedEvent->getTricks(),
                ]
            ), 'text/html');

        $this->mailer->send($mail);
    }

    /**
     * @param TricksRefusedEvent $refusedEvent
     *
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onRefuseTricks(TricksRefusedEvent $refusedEvent)
    {
        $mail = \Swift_Message::newInstance()
            ->setSubject('Snowtricks - Notification system')
            ->setFrom('contact@snowtricks.fr')
            ->setTo($refusedEvent->getTricks()->getAuthor()->getEmail())
            ->setBody($this->templating->render(
                ':Mails:refuse_tricks.html.twig', [
                    'tricks' => $refusedEvent->getTricks(),
                ]
            ), 'text/html');

        $this->mailer->send($mail);
    }
}
