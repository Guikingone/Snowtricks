<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Events\Commentary;

use AppBundle\Entity\Commentary;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CommentaryAddedEvent.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryAddedEvent extends Event
{
    const NAME = 'commentary.added';

    /** @var Commentary */
    public $commentary;

    /**
     * CommentaryAddedEvent constructor.
     *
     * @param Commentary $commentary
     */
    public function __construct(Commentary $commentary)
    {
        $this->commentary = $commentary;
    }

    /**
     * @return Commentary
     */
    public function getCommentary() : Commentary
    {
        return $this->commentary;
    }
}
