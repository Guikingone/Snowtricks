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

/**
 * Class CommentaryController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryController extends Controller
{
    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCommentariesByTricksAction(int $id)
    {
        return $this->get('api.commentary_manager')->getCommentariesByTricks($id);
    }

    /**
     * @param int $id
     * @param int $tricks
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSingleCommentaryByTricksAction(int $id, int $tricks)
    {
        return $this->get('api.commentary_manager')->getSingleCommentaryById($id, $tricks);
    }
}
