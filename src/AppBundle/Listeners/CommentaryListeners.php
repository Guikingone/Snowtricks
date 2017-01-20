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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

// Entity
use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;

// Events
use AppBundle\Events\TricksDeletedEvent;

/**
 * Class CommentaryListeners.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryListeners
{
    /**
     * @var TokenStorage
     */
    private $storage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * CommentaryListeners constructor.
     *
     * @param TokenStorage  $storage
     * @param RequestStack  $requestStack
     * @param TwigEngine    $templating
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        TokenStorage $storage,
        RequestStack $requestStack,
        TwigEngine $templating,
        \Swift_Mailer $mailer
    ) {
        $this->storage = $storage;
        $this->requestStack = $requestStack;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \InvalidArgumentException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Commentary) {
            return;
        }

        if ($tricks = $this->requestStack->getCurrentRequest()->get('name')) {
            // Find the tricks linked by the request.
            $object = $args->getObjectManager()->getRepository('AppBundle:Tricks')
                                               ->findOneBy([
                                                   'name' => $tricks,
                                               ]);

            if (is_object($object) && $object instanceof Tricks) {
                $entity->setPublicationDate(new \DateTime());
                $entity->setAuthor($this->storage->getToken()->getUser());
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
