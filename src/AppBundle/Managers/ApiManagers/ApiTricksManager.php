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

// Form
use AppBundle\Form\Type\TricksType;

// Events
use AppBundle\Events\Tricks\TricksAddedEvent;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
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
     * Return every tricks saved into json format.
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
     * @param string $name
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
     * In the case that the request don't contain any data, the response contain
     * a 400 (BAD REQUEST) headers code.
     *
     * @see Response::HTTP_BAD_REQUEST
     *
     * In the case that the resource already exist, the response contain
     * a 303 (SEE OTHER) headers code and the content of the resource found.
     *
     * @see Response::HTTP_SEE_OTHER
     *
     * In the case the resource does not exist, the response contain
     * a 201 (CREATED) headers code and the content of the resource created.
     *
     * @see Response::HTTP_CREATED
     *
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
     * Allow to put (aka update) a certain part of the resource find by his id.
     *
     * In the case that the resource isn't found, a new resource with the current
     * content passed through the request is created with the 201 (CREATED) headers code.
     *
     * @see Response::HTTP_CREATED
     *
     * In the case that the resource is created but has been found
     * due by a past persist, a 303 (SEE OTHER) headers code is return
     * with the state of the resource.
     *
     * @see Response::HTTP_SEE_OTHER
     *
     * In the case that the resource is find and could be updated,
     * the response send a 200 (OK) headers code
     * and the actual state of the resource.
     *
     * @see Response::HTTP_OK
     *
     * In the case that the resource could'nt been updated,
     * the response send a 204 (NO CONTENT) headers code.
     *
     * @see Response::HTTP_NO_CONTENT
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function putSingleTricks()
    {
        $id = $this->request->getCurrentRequest()->get('id');

        $trick = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'id' => $id,
                                ]);

        if (!$trick) {
            $tricks = new Tricks();

            $data = $this->request->getCurrentRequest()->request->all();

            // Init the workflow phase.
            $this->workflow->apply($tricks, 'start_phase');

            $form = $this->form->create(TricksType::class, $tricks, [
                'csrf_protection' => false,
            ]);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                // Search if a equivalent resource has been created.
                $object = $form->getData();
                $trick = $this->doctrine->getRepository(Tricks::class)
                                        ->findOneBy([
                                            'name' => $object->getName(),
                                        ]);

                if ($trick) {
                    return new JsonResponse(
                        [
                            'message' => 'Resource already found.',
                            'name' => $trick->getName(),
                            'groups' => $trick->getGroups(),
                            'resume' => $trick->getResume(),
                        ],
                        Response::HTTP_SEE_OTHER
                    );
                }

                // Send a new event to perform new instance actions.
                $event = new TricksAddedEvent($tricks);
                $this->dispatcher->dispatch(TricksAddedEvent::NAME, $event);

                $this->doctrine->persist($tricks);
                $this->doctrine->flush();

                return new JsonResponse(
                    [
                        'message' => 'Resource created',
                        'name' => $tricks->getName(),
                        'groups' => $tricks->getGroups(),
                        'resume' => $tricks->getResume(),
                    ],
                    Response::HTTP_CREATED
                );
            }
        }

        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(TricksType::class, $trick, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource updated',
                    'name' => $trick->getName(),
                    'groups' => $trick->getGroups(),
                    'resume' => $trick->getResume(),
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'message' => 'Resource not updated',
            ],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Allow to patch (aka update with minimal modifications) a single resource.
     *
     * In the case that the resource is found using his id, the data passed through
     * the request are send to the form and 'patch'
     * (aka update with minimal modifications) only the requested input,
     * once the patch is applied, the response is send using a 200 (OK) headers code
     * and the state of the resource after patch.
     *
     * @see Response::HTTP_OK
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function patchSingleTricks() : JsonResponse
    {
        $id = $this->request->getCurrentRequest()->get('id');

        $trick = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'id' => $id,
                                ]);

        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(TricksType::class, $trick, [
            'csrf_protection' => false,
        ]);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource updated',
                    'name' => $trick->getName(),
                    'groups' => $trick->getGroups(),
                    'resume' => $trick->getResume(),
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'message' => 'Resource not found',
            ],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Allow to delete a resource using his id stored inside the request.
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function deleteSingleTricks() : JsonResponse
    {
        $id = $this->request->getCurrentRequest()->get('id');

        $trick = $this->doctrine->getRepository(Tricks::class)
                                ->findOneBy([
                                    'id' => $id,
                                ]);

        if ($trick) {
            $this->doctrine->remove($trick);
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource deleted',
                ],
                Response::HTTP_NO_CONTENT
            );
        }

        return new JsonResponse(
            ['message' => 'Resource not found'],
            Response::HTTP_NOT_FOUND
        );
    }
}
