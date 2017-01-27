<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Managers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

// Entity
use UserBundle\Entity\User;

// Events
use UserBundle\Events\ConfirmedUserEvent;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserManager
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TraceableEventDispatcher
     */
    private $dispatcher;

    /**
     * UserManager constructor.
     *
     * @param EntityManager            $doctrine
     * @param Session                  $session
     * @param AuthorizationChecker     $security
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct(
        EntityManager $doctrine,
        Session $session,
        AuthorizationChecker $security,
        TraceableEventDispatcher $dispatcher
    ) {
        $this->doctrine = $doctrine;
        $this->session = $session;
        $this->security = $security;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Allow to return all the users.
     *
     * @return array|User[]
     */
    public function getUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findAll();
    }

    /**
     * Return a single user using his firstname.
     *
     * @param string $name
     *
     * @return null|User
     */
    public function getUser(string $name)
    {
        return $this->doctrine->getRepository('UserBundle:User')->findOneBy(['lastname' => $name]);
    }

    /**
     * Return every users not validated.
     *
     * @return array|User[]
     */
    public function getUsersNotValidated()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['validated' => false]);
    }

    /**
     * Return every users validated.
     *
     * @return array|User[]
     */
    public function getUsersValidated()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['validated' => true]);
    }

    /**
     * Return all the users locked.
     *
     * @return array|User[]
     */
    public function getLockedUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['locked' => true]);
    }

    /**
     * Return all the users unlocked.
     *
     * @return array|User[]
     */
    public function getUnlockedUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')->findBy(['locked' => false]);
    }

    /**
     * Allow to validate a user using the generated token.
     *
     * @param string $token
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return RedirectResponse
     */
    public function validateUser($token)
    {
        if (!is_string($token)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The token MUST be a integer !, 
                    given "%s"', gettype($token)
                )
            );
        }

        if ($token) {
            $user = $this->doctrine->getRepository('UserBundle:User')
                                    ->findOneBy([
                                        'token' => $token,
                                    ]);

            if (!$user) {
                throw new \LogicException(
                    sprintf(
                        'The token isn\'t valid !'
                    )
                );
            }

            if ($user->getToken() === $token) {
                $event = new ConfirmedUserEvent($user);
                $this->dispatcher->dispatch(ConfirmedUserEvent::NAME, $event);
            }
        }

        return new RedirectResponse('login');
    }

    /**
     * Allow to lock a user using his lastname.
     *
     * @param string $name
     *
     * @throws AccessDeniedException
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function lockUser(string $name)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException(
                sprintf(
                    'L\'accès à cette ressource est bloqué 
                    aux administrateurs !'
                )
            );
        }

        $user = $this->doctrine->getRepository('UserBundle:User')
                               ->findOneBy([
                                   'lastname' => $name,
                               ]);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The entity received MUST be a instance of User !,
                     given "%s"', get_class($user)
                )
            );
        }

        $user->setLocked(true);
        $user->setActive(false);

        $this->doctrine->flush();
        $this->session->getFlashBag()->add(
            'success',
            'L\'utilisateur a bien été bloqué.'
        );

        return new RedirectResponse('admin');
    }

    /**
     * Allow to unlock a user using his lastname and the boolean
     * of his lock phase.
     *
     * @param string $name
     *
     * @throws AccessDeniedException
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function unlockUser(string $name)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException(
                sprintf(
                    'L\'accès à cette ressource est bloqué 
                    aux administrateurs !'
                )
            );
        }

        $user = $this->doctrine->getRepository('UserBundle:User')
                               ->findOneBy([
                                   'lastname' => $name,
                                   'locked' => true,
                               ]);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The entity received MUST be a instance of User !,
                     given "%s"', get_class($user)
                )
            );
        }

        $user->setLocked(false);
        $user->setActive(true);

        $this->doctrine->flush();
        $this->session->getFlashBag()->add(
            'success',
            'L\'utilisateur a bien été débloqué.'
        );

        return new RedirectResponse('admin');
    }
}
