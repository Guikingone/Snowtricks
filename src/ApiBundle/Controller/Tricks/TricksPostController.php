<?php
/**
 * Created by PhpStorm.
 * User: Guillaume
 * Date: 03/02/2017
 * Time: 11:32
 */

namespace ApiBundle\Controller\Tricks;


use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class TricksPostController
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksPostController extends FOSRestController implements
      ClassResourceInterface
{
    /**
     * @param Request $request
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newTricksAction(Request $request)
    {
        $this->get('app.tricks_manager')->addTricksFromAPI($request);

        return $this->redirectToRoute('api_tricks', [], Response::HTTP_CREATED);
    }
}