<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Class SecurityController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityController extends Controller
{
    /**
     * @param Request $request
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws InvalidOptionsException
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $register = $this->get('user.security')->registerUser($request);

        return $this->render(':Security:register.html.twig', [
            'register' => $register,
        ]);
    }

    /**
     * @return Response
     */
    public function loginAction()
    {
        $security = $this->get('user.security')->loginUser();

        return $this->render(':Security:login.html.twig', [
            'error' => $security['errors'],
            'lastUsername' => $security['lastUsername'],
        ]);
    }
}
