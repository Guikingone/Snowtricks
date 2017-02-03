<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Controller\Tricks;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TricksController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getAllTricksAction()
    {
        $tricks = $this->get('app.tricks_manager')->getAllTricks();

        return new JsonResponse($tricks);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getSingleTricksAction(int $id)
    {
        $tricks = $this->get('app.tricks_manager')->getTricksById($id);

        return new JsonResponse($tricks);
    }
}
