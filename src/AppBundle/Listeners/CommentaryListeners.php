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

use AppBundle\Controller\Web\HomeController;
use AppBundle\Events\Commentary\CommentaryAddedEvent;
use AppBundle\Managers\CommentaryManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

// Events
use AppBundle\Events\Tricks\TricksDeletedEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @throws AccessDeniedException
     *
     * @see HomeController::tricksDetailsAction()
     * @see CommentaryManager::addCommentary()
     * @see CommentaryAddedEvent
     */
    public function onCommentaryAdded(CommentaryAddedEvent $addedEvent)
    {
        $entity = $addedEvent->getCommentary();

        if (is_object($entity)
            && $tricks = $this->requestStack->getCurrentRequest()->get('name')) {
            // Find the tricks linked by the request.
            $object = $this->doctrine->getRepository('AppBundle:Tricks')
                                     ->findOneBy([
                                         'name' => $tricks,
                                     ]);

            $user = $this->storage->getToken()->getUsername();

            $author = $this->doctrine->getRepository('UserBundle:User')
                                     ->findOneBy([
                                         'username' => $user,
                                     ]);

            if (!$author) {
                throw new AccessDeniedException(
                    sprintf(
                        'Vous devez être connecté pour poster un commentaire !'
                    )
                );
            }

            $entity->setPublicationDate(new \DateTime());
            $entity->setAuthor($author);
            $entity->setTricks($object);
            $object->addCommentary($entity);
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
        if ($commentaries = $deletedEvent->getTricks()->getCommentary()) {
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
}
