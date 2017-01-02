<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Events;

use AppBundle\Entity\Tricks;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TricksRefuseEvent.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksRefusedEvent extends Event
{
    /**
     * The name of the event.
     */
    const NAME = 'tricks.refused';

    /**
     * @var Tricks
     */
    protected $tricks;

    /**
     * TricksValidatedEvent constructor.
     *
     * @param Tricks $tricks
     */
    public function __construct(Tricks $tricks)
    {
        $this->tricks = $tricks;
    }

    /**
     * @return Tricks
     */
    public function getTricks() : Tricks
    {
        return $this->tricks;
    }
}
