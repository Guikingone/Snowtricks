<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersAction()
    {
        return $this->get('api.user_manager')->getUsers();
    }
}
