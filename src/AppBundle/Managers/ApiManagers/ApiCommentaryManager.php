<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Managers\ApiManagers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiCommentaryManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ApiCommentaryManager
{
    /** @var EntityManager */
    private $doctrine;

    public function __construct(
        EntityManager $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getCommentariesByTricks(int $id)
    {
        $commentaries = $this->doctrine->getRepository('AppBundle:Commentary')
                                       ->findBy([
                                           'tricks' => $id,
                                       ]);

        if (!$commentaries) {
            return new JsonResponse([
                'message' => 'Resources not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $data = [];

        foreach ($commentaries as $commentary) {
            $data[] = [
                'content' => $commentary->getContent(),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @param int $id
     * @param int $tricks
     *
     * @return JsonResponse
     */
    public function getSingleCommentaryById(int $id, int $tricks)
    {
        $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                     ->findOneBy([
                                         'id' => $id,
                                         'tricks' => $tricks,
                                     ]);

        if (!$commentary) {
            return new JsonResponse([
                'message' => 'Resource not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $data[] = [
            'id' => $commentary->getId(),
            'content' => $commentary->getContent(),
        ];

        return new JsonResponse($data);
    }
}
