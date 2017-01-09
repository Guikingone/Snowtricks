<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Services\Api;

use Symfony\Component\Serializer\Serializer;

/**
 * Class Api
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class Api
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Api constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct (Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
}