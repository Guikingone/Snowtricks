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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
    public function profileAction()
    {
        return $this->render(':Security/Users:profile.html.twig');
    }

    /**
     * @param string $name
     *
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     */
    public function userUnlockedAction(string $name)
    {
        $this->get('user.user_manager')->unlockUser($name);
    }

    /**
     * @param string $name
     *
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     */
    public function userLockedAction($name)
    {
        $this->get('user.user_manager')->lockUser($name);
    }

    /**
     * @param int $token
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function validateUserAction(int $token)
    {
        $this->get('user.user_manager')->validateUser($token);
    }
}
