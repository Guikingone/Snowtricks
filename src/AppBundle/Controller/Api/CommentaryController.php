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
use Symfony\Component\HttpFoundation\JsonResponse;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Class CommentaryController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryController extends Controller
{
    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getCommentariesAction()
    {
        return $this->get('api.commentary_manager')->getCommentaries();
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCommentariesByTricksAction(int $id) : JsonResponse
    {
        return $this->get('api.commentary_manager')->getCommentariesByTricks($id);
    }

    /**
     * @param int $id
     * @param int $tricks
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSingleCommentaryByTricksAction(int $id, int $tricks) : JsonResponse
    {
        return $this->get('api.commentary_manager')->getSingleCommentaryById($id, $tricks);
    }

    /**
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postNewCommentaryByTricksNameAction() : JsonResponse
    {
        return $this->get('api.commentary_manager')->postNewCommentary();
    }

    /**
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putSingleCommentaryByIdAction() : JsonResponse
    {
        return $this->get('api.commentary_manager')->putSingleCommentary();
    }

    /**
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSingleCommentaryByIdAction() : JsonResponse
    {
        return $this->get('api.commentary_manager')->deleteCommentary();
    }
}
