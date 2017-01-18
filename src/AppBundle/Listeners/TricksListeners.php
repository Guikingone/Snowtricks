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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;

// Event
use AppBundle\Events\TricksRefusedEvent;
use AppBundle\Events\TricksValidatedEvent;

// App services
use AppBundle\Services\Uploader;

// Exceptions
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
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
     * @var TokenStorage
     */
    private $storage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    // Store the images.upload.dir
    private $imagesDir;

    /**
     * TricksListeners constructor.
     *
     * @param Workflow             $workflow
     * @param Uploader             $uploader
     * @param AuthorizationChecker $security
     * @param TwigEngine           $templating
     * @param Session              $session
     * @param TokenStorage         $storage
     * @param RequestStack         $requestStack
     * @param \Swift_Mailer        $mailer
     * @param                      $imagesDir
     */
    public function __construct(
        Workflow $workflow,
        Uploader $uploader,
        AuthorizationChecker $security,
        TwigEngine $templating,
        Session $session,
        TokenStorage $storage,
        RequestStack $requestStack,
        \Swift_Mailer $mailer,
        $imagesDir
    ) {
        $this->workflow = $workflow;
        $this->uploader = $uploader;
        $this->security = $security;
        $this->templating = $templating;
        $this->session = $session;
        $this->storage = $storage;
        $this->requestStack = $requestStack;
        $this->mailer = $mailer;
        $this->imagesDir = $imagesDir;
    }

    /**
     * if the user is granted as ADMIN, we set the validation, publication
     * and date by default.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws AuthenticationCredentialsNotFoundException
     * @throws \LogicException
     * @throws InvalidDefinitionException
     * @throws FileException
     * @throws LogicException
     * @throws \InvalidArgumentException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Tricks || !$entity instanceof Commentary) {
            return;
        }

        // Normal case.
        if ($entity instanceof Tricks) {
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
        } elseif ($entity->getPublished() === false && $entity->getValidated()) {
            // In the case of entity update.
            $entity->setPublished(true);
        } else {
            // In the case of user submission.
            $entity->setValidated(false);
            $entity->setPublished(false);
        }

        if ($entity instanceof Commentary) {
            $request = $this->requestStack->getCurrentRequest()->get('name');
            // Find the tricks linked by the request.
            $name = $args->getObjectManager()->getRepository('AppBundle:Tricks')
                                             ->findOneBy([
                                                 'name' => $request,
                                             ]);

            if (is_object($name) && $name instanceof Tricks) {
                $entity->setAuthor($this->storage->getToken()->getUser());
                $entity->setTricks($name);
                $name->addCommentary($entity);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The entity MUST be a instance of Tricks !,
                         given "%s"', get_class($name)
                    )
                );
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'The entity MUST be a instance of Commentary !,
                         given "%s"', get_class($entity)
                )
            );
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
            $author = $args->getObjectManager()->getRepository('UserBundle:User')->findBy([
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
}
