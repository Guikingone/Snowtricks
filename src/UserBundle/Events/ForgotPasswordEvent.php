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

/**
 * Class ForgotPasswordEvent.
 */
class ForgotPasswordEvent extends Event
{
    /**
     * The name of the Event.
     */
    const NAME = 'forgot.password';

    /**
     * @var array
     */
    protected $data;

    /**
     * ForgotPasswordEvent constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }
}
