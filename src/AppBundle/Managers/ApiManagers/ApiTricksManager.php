<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Managers\ApiManagers;

use AppBundle\Form\Type\TricksType;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Tricks;

// Events
use AppBundle\Events\Tricks\TricksAddedEvent;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class ApiTricksManager
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ApiTricksManager
{
    /** @var EntityManager */
    private $doctrine;

    /** @var FormFactory */
    private $form;

    /** @var TraceableEventDispatcher */
    private $dispatcher;

    /** @var Workflow */
    private $workflow;

    /** @var ViewHandler */
    private $viewHandler;

    /** @var RequestStack */
    private $request;

    /**
     * ApiTricksManager constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param TraceableEventDispatcher $dispatcher
     * @param Workflow                 $workflow
     * @param ViewHandler              $handler
     * @param RequestStack             $requestStack
     */
    public function __construct (
        EntityManager $doctrine,
        FormFactory $form,
        TraceableEventDispatcher $dispatcher,
        Workflow $workflow,
        ViewHandler $handler,
        RequestStack $requestStack
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->dispatcher = $dispatcher;
        $this->workflow = $workflow;
        $this->viewHandler = $handler;
        $this->request = $requestStack;
    }

    /**
     * Return every tricks saved into json format.
     *
     * @throws UnsupportedMediaTypeHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllTricks()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')->findAll();

        if (!$tricks) {
            return new JsonResponse([
                'message' => 'Resources not found',
                Response::HTTP_NOT_FOUND
            ]);
        }

        $data = [];
        foreach ($tricks as $trick) {
            $data[] = [
                'id' => $trick->getId(),
                'name' => $trick->getName(),
                'groups' => $trick->getGroups(),
                'resume' => $trick->getResume(),
                'published' => $trick->getPublished()
            ];
        }

        $view = View::create($data);
        $view->setFormat('json');

        return $this->viewHandler->handle($view);
    }

    /**
     * @param int $id
     *
     * @throws UnsupportedMediaTypeHttpException
     *
     * @return JsonResponse|Response
     */
    public function getSingleTricks(int $id)
    {
        $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                ->findOneBy([
                                    'id' => $id
                                ]);

        if (!$trick) {
            return new JsonResponse([
                'message' => 'Resource not found',
                Response::HTTP_NOT_FOUND
            ]);
        }

        $view = View::create($trick);
        $view->setFormat('json');

        return $this->viewHandler->handle($view);
    }

    /**
     * Create a new Tricks using the data's passed through the request.
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return \Symfony\Component\Form\FormInterface|JsonResponse
     */
    public function postNewTricks()
    {
        $tricks = new Tricks();

        // Init the workflow phase.
        $this->workflow->apply($tricks, 'start_phase');

        // Grab the data passed through the request.
        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(TricksType::class, $tricks, [
            'csrf_protection' => false
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Send a new event to perform new instance actions.
            $event = new TricksAddedEvent($tricks);
            $this->dispatcher->dispatch(TricksAddedEvent::NAME, $event);

            $this->doctrine->persist($tricks);
            $this->doctrine->flush();

            return new JsonResponse([
                $data,
                'message' => 'Resource created',
                Response::HTTP_CREATED
            ]);
        }

        return $form;
    }
}