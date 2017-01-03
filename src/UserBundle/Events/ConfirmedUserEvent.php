<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use UserBundle\Entity\User;

/**
 * Class ConfirmedUserEvent.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ConfirmedUserEvent extends Event
{
    /**
     * The name of the Event.
     */
    const NAME = 'user.validated';

    /**
     * @var User
     */
    protected $user;

    /**
     * ConfirmedUserEvent constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }
}
