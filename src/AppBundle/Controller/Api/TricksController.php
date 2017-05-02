<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Responses
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class TricksController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksController extends Controller
{
    /**
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTricksAction() : Response
    {
        return $this->get('api.tricks_manager')->getAllTricks();
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getTricksByNameAction(string $name)
    {
        return $this->get('api.tricks_manager')->getSingleTricks($name);
    }

    /**
     * @throws \LogicException
     * @throws LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function postTricksAction() : JsonResponse
    {
        return $this->get('api.tricks_manager')->postNewTricks();
    }

    /**
     * @param $id
     *
     * @throws \LogicException
     * @throws LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function putSingleTricksAction($id) : JsonResponse
    {
        return $this->get('api.tricks_manager')->putSingleTricks($id);
    }

    /**
     * @param $id
     *
     * @throws \LogicException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function patchSingleTricksAction($id) : JsonResponse
    {
        return $this->get('api.tricks_manager')->patchSingleTricks($id);
    }

    /**
     * @param $id
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function deleteTricksByIdAction($id) : JsonResponse
    {
        return $this->get('api.tricks_manager')->deleteSingleTricks($id);
    }
}
