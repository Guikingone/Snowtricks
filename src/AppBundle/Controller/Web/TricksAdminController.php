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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class TricksAdminController.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksAdminController extends Controller
{
    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws LogicException
     */
    public function tricksValidationAction($name)
    {
        $this->get('app.tricks_manager')->validateTricks($name);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function tricksRefusedAction($name)
    {
        $this->get('app.tricks_manager')->refuseTricks($name);
    }

    /**
     * @param Request $request
     * @param string  $name
     *
     * @throws \LogicException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksUpdateAction(Request $request, $name)
    {
        $tricks = $this->get('app.tricks_manager')->updateTricks($request, $name);

        return $this->render(':Back:tricks_update.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function tricksDeleteAction(string $name)
    {
        $this->get('app.tricks_manager')->deleteTricks($name);
    }
}
