<?php

/*
 * This file is part of the $PROJECT project.
 *
 * (c) Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Responder;

use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class Responder
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Responder
{
    /** @var SerializerInterface */
    private $serializer;

    /**
     * Responder constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct (
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function __invoke ($data)
    {

    }
}