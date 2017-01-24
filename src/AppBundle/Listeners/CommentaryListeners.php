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

use AppBundle\Events\Commentary\CommentaryAddedEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

// Entity
use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;

// Events
use AppBundle\Events\Tricks\TricksDeletedEvent;

/**
 * Class CommentaryListeners.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryListeners
{
    /** @var EntityManager */
    private $doctrine;

    /** @var TokenStorage */
    private $storage;

    /** @var RequestStack */
    private $requestStack;

    /** @var TwigEngine */
    private $templating;

    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * CommentaryListeners constructor.
     *
     * @param EntityManager $doctrine
     * @param TokenStorage  $storage
     * @param RequestStack  $requestStack
     * @param TwigEngine    $templating
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        EntityManager $doctrine,
        TokenStorage $storage,
        RequestStack $requestStack,
        TwigEngine $templating,
        \Swift_Mailer $mailer
    ) {
        $this->doctrine = $doctrine;
        $this->storage = $storage;
        $this->requestStack = $requestStack;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    /**
     * @param CommentaryAddedEvent $addedEvent
     *
     * @throws \InvalidArgumentException
     */
    public function onCommentaryAdded(CommentaryAddedEvent $addedEvent)
    {
        $entity = $addedEvent->getCommentary();

        if (!$entity instanceof Commentary) {
            return;
        }

        if ($tricks = $this->requestStack->getCurrentRequest()->get('name')) {
            // Find the tricks linked by the request.
            $object = $this->doctrine->getRepository('AppBundle:Tricks')
                                     ->findOneBy([
                                         'name' => $tricks,
                                     ]);

            $user = $this->storage->getToken()->getUser();

            $author = $this->doctrine->getRepository('UserBundle:User')
                                     ->findOneBy([
                                         'username' => $user,
                                     ]);

            if (is_object($object) && $object instanceof Tricks) {
                $entity->setPublicationDate(new \DateTime());
                $entity->setAuthor($author);
                $entity->setTricks($object);
                $object->addCommentary($entity);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The entity MUST be a instance of Tricks !,
                         given "%s"', get_class($object)
                    )
                );
            }
        }
    }

    /**
     * Allow to notify every person who post a comment
     * linked to the tricks deleted.
     *
     * @param TricksDeletedEvent $deletedEvent
     *
     * @throws \RuntimeException
     * @throws \Twig_Error
     */
    public function onDeleteTricks(TricksDeletedEvent $deletedEvent)
    {
        $commentaries = $deletedEvent->getTricks()->getCommentary();

        foreach ($commentaries as $commentary) {
            // Notify every person who post a comment.
            $mail = \Swift_Message::newInstance()
                ->setSubject('Snowtricks - Notification system')
                ->setFrom('contact@snowtricks.fr')
                ->setTo($commentary->getAuthor()->getEmail())
                ->setBody($this->templating->render(
                    ':Mails:delete_tricks.html.twig', [
                        'tricks' => $deletedEvent->getTricks(),
                    ]
                ), 'text/html');

            $this->mailer->send($mail);
        }
    }
}
