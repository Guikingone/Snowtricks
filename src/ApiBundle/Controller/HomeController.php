<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class HomeController extends Controller
{
    public function homeAction()
    {
        return new Response('Hello !');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        return $this->render(':ApiBundle/Security:login.html.twig');
    }
}
