<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Responder;

use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

/**
 * Class Responder
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class JsonResponder
{
    /** @var Serializer */
    private $serializer;

    /**
     * Responder constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct (Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string   $message  The message needed for the response.
     * @param mixed    $data     The data passed through the response.
     * @param integer  $response The headers code passed to the response.
     *
     * @throws \InvalidArgumentException Thrown by the Response class.
     *
     * @return Response          The whole response with the headers.
     */
    public function __invoke ($message, $data, $response)
    {
        $data = $this->serializer->serialize($data, 'json');

        return new Response(
            $data,
            $response,
            ['Content-Type' => 'application/json']
        );
    }
}
