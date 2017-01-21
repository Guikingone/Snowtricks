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

use AppBundle\Events\TricksDeletedEvent;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Tricks;

// Event
use AppBundle\Events\TricksRefusedEvent;
use AppBundle\Events\TricksValidatedEvent;

// App services
use AppBundle\Services\Uploader;

// Exceptions
use Symfony\Component\Workflow\Exception\InvalidDefinitionException;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

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
     * @var Uploader
     */
    private $uploader;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    // Store the images.upload.dir
    private $imagesDir;

    /**
     * TricksListeners constructor.
     *
     * @param Workflow      $workflow
     * @param Uploader      $uploader
     * @param TwigEngine    $templating
     * @param Session       $session
     * @param \Swift_Mailer $mailer
     * @param               $imagesDir
     */
    public function __construct(
        Workflow $workflow,
        Uploader $uploader,
        TwigEngine $templating,
        Session $session,
        \Swift_Mailer $mailer,
        $imagesDir
    ) {
        $this->workflow = $workflow;
        $this->uploader = $uploader;
        $this->templating = $templating;
        $this->session = $session;
        $this->mailer = $mailer;
        $this->imagesDir = $imagesDir;
    }

    /**
     * if the user is granted as ADMIN, we set the validation, publication
     * and date by default.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     * @throws \LogicException
     * @throws InvalidDefinitionException
     * @throws FileException
     * @throws LogicException
     * @throws \InvalidArgumentException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Tricks) {
            return;
        }

        // Update case.
        if ($entity->getValidated() && !$entity->getPublished()) {
            $admins = $args->getObjectManager()->getRepository('UserBundle:User')
                                               ->findBy([
                                                   'roles' => 'ROLE_ADMIN',
                                               ]);
            $entity->setPublished(true);

            foreach ($admins as $admin) {
                // Send the update to every admins for future validations.
                $mail = \Swift_Message::newInstance()
                    ->setSubject('Snowtricks - Notification system')
                    ->setFrom('contact@snowtricks.fr')
                    ->setTo($admin->getEmail())
                    ->setBody($this->templating->render(
                        ':Mails:notif_update_tricks.html.twig', [
                            'tricks' => $entity,
                        ]
                    ), 'text/html');

                $this->mailer->send($mail);
            }
        }

        // Normal case.
        if (array_key_exists('validation', $entity->currentState)) {
            $entity->setCreationDate(new \DateTime());
            $entity->setValidated(true);
            $entity->setPublished(true);

            // Set the workflow phase.
            $this->workflow->apply($entity, 'validation_phase');

            // File processing.
            $images = $entity->getImages();
            if (is_array($images)) {
                foreach ($images as $img) {
                    $filename = $this->uploader->uploadFile($img);
                    $entity->addImage($filename);
                }
            }
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
            return;
        }

        if ($entity->getPublished() && $entity->getValidated() === true) {

            // Find the admins stored to send emails.
            $author = $args->getObjectManager()->getRepository('UserBundle:User')
                                               ->findBy([
                                                   'roles' => 'ROLE_ADMIN',
                                               ]);

            // Finalize the workflow.
            $this->workflow->apply($entity, 'publication_phase');

            $this->session->getFlashBag()->add(
                'success',
                'Le trick a bien été enregistré'
            );
            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($author)
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
     * @param LifecycleEventArgs $args
     *
     * @throws FileNotFoundException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Tricks) {
            return;
        }

        // Only if the entity has store files.
        if ($entity->getImages()) {
            $filename = $entity->getImages();

            foreach ($filename as $file) {
                $entity->addImage(new File($this->imagesDir.'/'.$file));
            }
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

    /**
     * @param TricksDeletedEvent $deletedEvent
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onDeleteTricks(TricksDeletedEvent $deletedEvent)
    {
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
