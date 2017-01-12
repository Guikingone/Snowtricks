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
    public function adminAction()
    {
        return $this->render(':Back:admin_index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksAction()
    {
        return $this->render(':Back:admin_tricks.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction()
    {
        return $this->render(':Back:admin_users.html.twig');
    }
}
