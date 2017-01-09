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

/**
 * Class ApiMainController
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ApiMainController extends Controller
{
    /**
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTricksAction()
    {
        $tricks = $this->get('app.back')->getAllTricks();

        return new JsonResponse($tricks);
    }
}