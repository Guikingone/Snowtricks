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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class MainController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class MainController extends Controller
{
    public function homeAction(Request $request)
    {
        $name = $request->get('name');

        $user = $this->get('user.user_manager')->getUser($name);

        if (!$user) {
            throw new ResourceNotFoundException();
        }
    }
}
