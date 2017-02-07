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

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
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
    public function getTricksAction()
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
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\Form\FormInterface|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postTricksAction()
    {
        return $this->get('api.tricks_manager')->postNewTricks();
    }

    /**
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putSingleTricksAction()
    {
        return $this->get('api.tricks_manager')->putSingleTricks();
    }

    /**
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function patchSingleTricksAction()
    {
        return $this->get('api.tricks_manager')->patchSingleTricks();
    }

    /**
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteTricksByIdAction()
    {
        return $this->get('api.tricks_manager')->deleteSingleTricks();
    }
}
