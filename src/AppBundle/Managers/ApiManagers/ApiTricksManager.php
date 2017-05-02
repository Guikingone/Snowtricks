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

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Tricks;

// Events
use AppBundle\Events\Tricks\TricksAddedEvent;
use AppBundle\Events\Tricks\TricksDeletedEvent;
use AppBundle\Events\Tricks\TricksUpdatedEvent;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class ApiTricksManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ApiTricksManager
{
    /** @var Serializer */
    private $serializer;

    /** @var EntityManager */
    private $doctrine;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var Workflow */
    private $workflow;

    /** @var RequestStack */
    private $request;

    /**
     * ApiTricksManager constructor.
     *
     * @param Serializer               $serializer
     * @param EntityManager            $doctrine
     * @param EventDispatcherInterface $dispatcher
     * @param Workflow                 $workflow
     * @param RequestStack             $requestStack
     */
    public function __construct(
        Serializer $serializer,
        EntityManager $doctrine,
        EventDispatcherInterface $dispatcher,
        Workflow $workflow,
        RequestStack $requestStack
    ) {
        $this->serializer = $serializer;
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
        $this->workflow = $workflow;
        $this->request = $requestStack;
    }

    /**
     * Return all the tricks.
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllTricks() : Response
    {
        $data = $this->doctrine->getRepository(Tricks::class)
                                 ->findAll();

        if (!$data) {
            return new JsonResponse([
                'message' => 'Resources not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $tricks = $this->serializer->serialize(
            $data,
            'json',
            ['groups' => ['tricks']]
        );

        return new Response(
            $tricks,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Return a single tricks using his name.
     *
     * @param string $name                  The name of the Tricks.
     *
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse|Response
     */
    public function getSingleTricks(string $name)
    {
        $data = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'name' => $name,
                                ]);

        if (!$data) {
            return new JsonResponse([
                'message' => 'Resource not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $tricks = $this->serializer->serialize(
            $data,
            'json',
            ['groups' => ['tricks']]
        );

        return new Response(
            $tricks,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create a new Tricks using the data's passed through the request.
     *
     * @throws \LogicException
     * @throws LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function postNewTricks() : JsonResponse
    {
        $data = $this->request->getCurrentRequest()->getContent();

        if (!$data) {
            return new JsonResponse(
                ['message' => 'No data passed.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $trick = $this->serializer->deserialize(
            $data,
            Tricks::class,
            'json'
        );

        $clone = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'name' => $trick->getName()
                                ]);

        if ($clone) {
            return new JsonResponse(
                [
                    'message' => 'Resource already exist.',
                    'data' => $clone
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        $this->workflow->apply($trick, 'start_phase');

        $event = new TricksAddedEvent($trick);
        $this->dispatcher->dispatch(TricksAddedEvent::NAME, $event);

        $this->doctrine->persist($trick);
        $this->doctrine->flush();

        return new JsonResponse(
            [
                'message' => 'Resource created and saved.',
                'data' => $trick
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * Allow to update a Tricks using his id.
     *
     * @param int $id               The id of the Tricks updated.
     *
     * @throws \LogicException
     * @throws LogicException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function putSingleTricks($id) : JsonResponse
    {
        $tricks = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'id' => $id,
                                ]);

        if (!$tricks) {
            $clone = $this->request->getCurrentRequest()->getContent();

            $trick = $this->serializer->deserialize(
                $clone,
                Tricks::class,
                'json'
            );
            $this->workflow->apply($trick, 'start_phase');

            $event = new TricksAddedEvent($trick);
            $this->dispatcher->dispatch(TricksAddedEvent::NAME, $event);

            $this->doctrine->persist($trick);
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource created.',
                    'data' => $trick
                ],
                Response::HTTP_CREATED
            );
        }

        $data = $this->request->getCurrentRequest()->getContent();

        if (!$data) {
            return new JsonResponse(
                [
                    'message' => 'No data passed',
                    'data' => $tricks
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->serializer->deserialize(
            $data,
            $tricks,
            'json'
        );

        $event = new TricksUpdatedEvent($tricks);
        $this->dispatcher->dispatch(TricksUpdatedEvent::NAME, $event);

        $this->doctrine->flush();

        return new JsonResponse(
            [
                'message' => 'Resource updated.',
                'data' => $tricks
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Allow to patch a Tricks using the data passed trough the request.
     *
     * @param int $id                   The id of the tricks patched.
     *
     * @throws \LogicException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function patchSingleTricks($id) : JsonResponse
    {
        $tricks = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'id' => $id,
                                ]);

        $data = $this->request->getCurrentRequest()->getContent();

        $this->serializer->deserialize(
            $data,
            $tricks,
            'json'
        );

        $event = new TricksUpdatedEvent($tricks);
        $this->dispatcher->dispatch(TricksUpdatedEvent::NAME, $event);

        $this->doctrine->flush();

        return new JsonResponse(
            [
                'message' => 'Resource patched.',
                'data' => $tricks
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Allow to delete a resource using his id.
     *
     * @param int $id                       The id of the tricks deleted.
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function deleteSingleTricks($id) : JsonResponse
    {
        $tricks = $this->doctrine->getRepository(Tricks::class)
                                 ->findOneBy([
                                     'id' => $id,
                                 ]);

        if ($tricks) {
            $event = new TricksDeletedEvent($tricks);
            $this->dispatcher->dispatch(TricksDeletedEvent::NAME, $event);

            $this->doctrine->remove($tricks);
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource deleted',
                ],
                Response::HTTP_NO_CONTENT
            );
        }

        return new JsonResponse(
            [
                'message' => 'Resource not found'
            ],
            Response::HTTP_NOT_FOUND
        );
    }
}
