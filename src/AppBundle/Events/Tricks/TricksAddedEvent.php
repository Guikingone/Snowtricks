<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Events\Tricks;

use AppBundle\Entity\Tricks;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TricksAddedEvent.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksAddedEvent extends Event
{
    const NAME = 'tricks.added';

    /** @var Tricks */
    protected $tricks;

    /**
     * TricksAddedEvent constructor.
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
