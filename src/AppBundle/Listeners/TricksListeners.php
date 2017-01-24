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

use AppBundle\Events\Tricks\TricksAddedEvent;
use AppBundle\Events\Tricks\TricksDeletedEvent;
use AppBundle\Events\Tricks\TricksUpdatedEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Workflow\Workflow;

// Event
use AppBundle\Events\Tricks\TricksRefusedEvent;
use AppBundle\Events\Tricks\TricksValidatedEvent;

// App services
use AppBundle\Services\Uploader;

// Exceptions
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Class TricksListeners.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksListeners
{
    /** @var EntityManager */
    private $doctrine;

    /** @var Workflow */
    private $workflow;

    /** @var Uploader */
    private $uploader;

    /** @var TwigEngine */
    private $templating;

    /** @var AuthorizationChecker */
    private $security;

    /** @var Session */
    private $session;

    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * TricksListeners constructor.
     *
     * @param EntityManager        $doctrine
     * @param Workflow             $workflow
     * @param Uploader             $uploader
     * @param TwigEngine           $templating
     * @param AuthorizationChecker $security
     * @param Session              $session
     * @param \Swift_Mailer        $mailer
     */
    public function __construct(
        EntityManager $doctrine,
        Workflow $workflow,
        Uploader $uploader,
        TwigEngine $templating,
        AuthorizationChecker $security,
        Session $session,
        \Swift_Mailer $mailer
    ) {
        $this->doctrine = $doctrine;
        $this->workflow = $workflow;
        $this->uploader = $uploader;
        $this->templating = $templating;
        $this->security = $security;
        $this->session = $session;
        $this->mailer = $mailer;
    }

    /**
     * By default, a new Tricks is always validated and published if the author
     * is considered as a admin, in the other case,
     * the tricks is send to validation.
     *
     * @param TricksAddedEvent $addedEvent
     *
     * @throws LogicException
     * @throws FileException
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onNewTricks(TricksAddedEvent $addedEvent)
    {
        $entity = $addedEvent->getTricks();

        if (is_object($entity)
            && array_key_exists('validation', $entity->currentState)
            && $this->security->isGranted('ROLE_ADMIN')) {
            $entity->setCreationDate(new \DateTime());
            $entity->setValidated(true);
            $entity->setPublished(true);

            // Set the workflow phase.
            $this->workflow->apply($entity, 'validation_phase');
            $this->workflow->apply($entity, 'publication_phase');

            // File processing.
            $images = $entity->getImages();
            if (is_array($images)) {
                foreach ($images as $img) {
                    $filename = $this->uploader->uploadFile($img);
                    $entity->addImage($filename);
                }
            }

            $this->session->getFlashBag()->add(
                'success',
                'Le trick a bien été enregistré et publié.'
            );
        } else {
            $entity->setCreationDate(new \DateTime());
            $entity->setValidated(false);
            $entity->setPublished(false);

            // File processing.
            $images = $entity->getImages();
            if (is_array($images)) {
                foreach ($images as $img) {
                    $filename = $this->uploader->uploadFile($img);
                    $entity->addImage($filename);
                }
            }

            $this->session->getFlashBag()->add(
                'success',
                'Le trick a été envoyé en validation.'
            );
        }
    }

    /**
     * @param TricksUpdatedEvent $updatedEvent
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onUpdateTricks(TricksUpdatedEvent $updatedEvent)
    {
        $entity = $updatedEvent->getTricks();

        if (is_object($entity) && !$entity->getPublished()) {
            $author = $entity->getAuthor();
            $admins = $this->doctrine->getRepository('UserBundle:User')
                                     ->findBy([
                                         'roles' => 'ROLE_ADMIN',
                                     ]);

            // Take the tricks back online.
            $entity->setPublished(true);

            $this->session->getFlashBag()->add(
                'success',
                'Le trick a bien été modifié.'
            );

            foreach ($admins as $admin) {
                // Send the update to every admins for future validations.
                $mail = \Swift_Message::newInstance()
                    ->setSubject('Snowtricks - Notification system')
                    ->setFrom('contact@snowtricks.fr')
                    ->setTo($author->getEmail())
                    ->setTo($admin->getEmail())
                    ->setBody($this->templating->render(
                        ':Mails:notif_update_tricks.html.twig', [
                            'tricks' => $entity,
                        ]
                    ), 'text/html');

                $this->mailer->send($mail);
            }
        }
    }

    /**
     * @param TricksValidatedEvent $validatedEvent
     *
     * @throws LogicException
     * @throws OptimisticLockException
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onValidateTricks(TricksValidatedEvent $validatedEvent)
    {
        $entity = $validatedEvent->getTricks();

        // Validate the trick.
        $entity->setValidated(true);

        // Find the admins stored to send emails.
        $admins = $this->doctrine->getRepository('UserBundle:User')
                                 ->findBy([
                                     'roles' => 'ROLE_ADMIN',
                                 ]);

        // Finalize the workflow.
        $this->workflow->apply($entity, 'validation_phase');
        $this->workflow->apply($entity, 'publication_phase');

        $this->doctrine->flush();

        $this->session->getFlashBag()->add(
            'success',
            'Le trick a bien été enregistré'
        );
        foreach ($admins as $admin) {
            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($admin->getEmail())
                ->setBody($this->templating->render(
                    ':Mails:notif_email.html.twig', [
                        'tricks' => $entity,
                    ]
                ), 'text/html');

            $this->mailer->send($mail);
        }

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
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onRefuseTricks(TricksRefusedEvent $refusedEvent)
    {
        $entity = $refusedEvent->getTricks();

        if (is_object($entity)) {
            $entity->setPublished(false);
            $entity->setValidated(false);

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

            $this->session->getFlashBag()->add(
                'infos',
                'Le tricks a été refusé et supprimé.'
            );
        }
    }

    /**
     * @param TricksDeletedEvent $deletedEvent
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onDeleteTricks(TricksDeletedEvent $deletedEvent)
    {
        $this->session->getFlashBag()->add(
            'infos',
            'Le tricks a été supprimé.'
        );

        $mail = \Swift_Message::newInstance()
            ->setSubject('Snowtricks - Notification system')
            ->setFrom('contact@snowtricks.fr')
            ->setTo($deletedEvent->getTricks()->getAuthor()->getEmail())
            ->setBody($this->templating->render(
                ':Mails:delete_tricks.html.twig', [
                    'tricks' => $deletedEvent->getTricks(),
                ]
            ), 'text/html');

        $this->mailer->send($mail);
    }
}
