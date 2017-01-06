<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

// Exceptions
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;

/**
 * Class HomeController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class HomeController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $tricks = $this->get('app.back')->getAllTricks();

        return $this->render('Home/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksAction()
    {
        $tricks = $this->get('app.back')->getAllTricks();

        return $this->render(':Home:tricks.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @param Request $request
     * @param string  $name
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws InvalidOptionsException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksDetailsAction(Request $request, string $name)
    {
        $trick = $this->get('app.back')->getTricksByName($name);

        $commentaryForm = $this->get('app.back')->addCommentary($request);

        return $this->render(':Home:tricks_details.html.twig', [
            'trick' => $trick,
            'commentaryForm' => $commentaryForm,
        ]);
    }
}
