<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class AdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminAction() : Response
    {
        return $this->render(':Back:admin_index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksAction() : Response
    {
        $tricks = $this->get('app.tricks_manager')->getAllTricks();

        return $this->render(':Back:admin_tricks.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction() : Response
    {
        $users = $this->get('user.user_manager')->getUsers();

        return $this->render(':Back:admin_users.html.twig', [
            'users' => $users,
        ]);
    }
}
