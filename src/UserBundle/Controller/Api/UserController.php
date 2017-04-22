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
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserController extends Controller
{
    /**
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersAction() : Response
    {
        return $this->get('api.user_manager')->getUsers();
    }

    /**
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSingleUserAction(string $name) : Response
    {
        return $this->get('api.user_manager')->getUser($name);
    }
}
