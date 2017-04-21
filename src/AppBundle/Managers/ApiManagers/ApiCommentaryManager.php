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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

// Entity
use AppBundle\Entity\Commentary;

// Events
use AppBundle\Events\Commentary\CommentaryAddedEvent;

// Form
use AppBundle\Form\Type\CommentaryType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;

/**
 * Class ApiCommentaryManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ApiCommentaryManager
{
    /** @var Serializer */
    private $serializer;

    /** @var EntityManager */
    private $doctrine;

    /** @var FormFactory */
    private $form;

    /** @var RequestStack */
    private $request;

    /** @var TraceableEventDispatcher */
    private $dispatcher;

    /**
     * ApiCommentaryManager constructor.
     *
     * @param Serializer               $serializer
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param RequestStack             $request
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct(
        Serializer $serializer,
        EntityManager $doctrine,
        FormFactory $form,
        RequestStack $request,
        TraceableEventDispatcher $dispatcher
    ) {
        $this->serializer = $serializer;
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse|Response
     */
    public function getCommentaries()
    {
        $commentaries = $this->doctrine->getRepository(Commentary::class)->findAll();

        if (!$commentaries) {
            return new JsonResponse([
                'message' => 'Resource not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $data = $this->serializer->serialize($commentaries, 'json', ['groups' => ['commentaries']]);

        return new Response(
            $data,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse|Response
     */
    public function getCommentariesByTricks(int $id)
    {
        $commentaries = $this->doctrine->getRepository(Commentary::class)
                                       ->findBy([
                                           'tricks' => $id,
                                       ]);

        if (!$commentaries) {
            return new JsonResponse([
                'message' => 'Resources not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $data = $this->serializer->serialize($commentaries, 'json');

        return new Response(
            $data,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param int $id
     * @param int $tricks
     *
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse|Response
     */
    public function getSingleCommentaryById(int $id, int $tricks)
    {
        $commentary = $this->doctrine->getRepository(Commentary::class)
                                     ->findOneBy([
                                         'id' => $id,
                                         'tricks' => $tricks,
                                     ]);

        if (!$commentary) {
            return new JsonResponse([
                'message' => 'Resource not found',
                Response::HTTP_NOT_FOUND,
            ]);
        }

        $data = $this->serializer->serialize($commentary, 'json');

        return new Response(
            $data,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create a new Commentary using the data's passed through the request.
     *
     * In the case that the resource already exist, the response contain
     * a 303 (SEE OTHER) headers code and the content of the resource found.
     *
     * @see Response::HTTP_SEE_OTHER
     *
     * In the case the resource does not exist, the response contain
     * a 201 (CREATED) headers code and the content of the resource created
     * @see Response::HTTP_CREATED
     *
     * In the case that the form isn't valid, the response contain
     * a 400 (BAD REQUEST) headers code
     * @see Response::HTTP_BAD_REQUEST
     *
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function postNewCommentary() : JsonResponse
    {
        $commentary = new Commentary();

        // Grab the data passed through the request.
        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(CommentaryType::class, $commentary, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Search if a equivalent resource has been created.
            $data = $form->getData();
            $object = $this->doctrine->getRepository(Commentary::class)
                                           ->findOneBy([
                                               'content' => $data->getContent(),
                                           ]);

            if ($object) {
                return new JsonResponse(
                    [
                        'message' => 'Resource already found.',
                        'content' => $object->getContent(),
                        'tricks' => $object->getTricks(),
                        'author' => $object->getAuthor(),
                    ],
                    Response::HTTP_SEE_OTHER
                );
            }

            // Send a new event to perform new instance actions.
            $event = new CommentaryAddedEvent($commentary);
            $this->dispatcher->dispatch(CommentaryAddedEvent::NAME, $event);

            $this->doctrine->persist($commentary);
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource created',
                    'author' => $commentary->getAuthor(),
                    'content' => $commentary->getContent(),
                    'date' => $commentary->getPublicationDate(),
                ],
                Response::HTTP_CREATED
            );
        }

        return new JsonResponse(
            [
                'message' => 'Form invalid',
            ],
            Response::HTTP_BAD_REQUEST
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
     * with the state of the resource
     * @see Response::HTTP_SEE_OTHER
     *
     * In the case that the resource is find and could be updated,
     * the response send a 200 (OK) headers code
     * and the actual state of the resource
     * @see Response::HTTP_OK
     *
     * In the case that the resource could'nt been updated,
     * the response send a 204 (NO CONTENT) headers code
     * @see Response::HTTP_NO_CONTENT
     *
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function putSingleCommentary() : JsonResponse
    {
        $id = $this->request->getCurrentRequest()->get('id');

        $commentary = $this->doctrine->getRepository(Commentary::class)
                                ->findOneBy([
                                    'id' => $id,
                                ]);

        if (!$commentary) {
            $commentary = new Commentary();

            $form = $this->form->create(CommentaryType::class, $commentary, [
                'csrf_protection' => false,
            ]);
            $form->submit($commentary);

            if ($form->isSubmitted() && $form->isValid()) {
                // Search if a equivalent resource has been created.
                $data = $form->getData();
                $commentary = $this->doctrine->getRepository(Commentary::class)
                                        ->findOneBy([
                                            'content' => $data->getContent(),
                                        ]);

                if ($commentary) {
                    return new JsonResponse(
                        [
                            'message' => 'Resource already found.',
                            'content' => $commentary->getContent(),
                        ],
                        Response::HTTP_SEE_OTHER
                    );
                }

                // Send a new event to perform new instance actions.
                $event = new CommentaryAddedEvent($commentary);
                $this->dispatcher->dispatch(CommentaryAddedEvent::NAME, $event);

                $this->doctrine->persist($commentary);
                $this->doctrine->flush();

                return new JsonResponse(
                    [
                        'message' => 'Resource created',
                        'content' => $commentary->getContent(),
                    ],
                    Response::HTTP_CREATED
                );
            }
        }

        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(CommentaryType::class, $commentary, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    'message' => 'Resource updated',
                    'content' => $commentary->getContent(),
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
     * Allow to delete a resource using his id.
     *
     * In the case that the resource is found, the resource is deleted
     * and the response is send using a 204 (NO_CONTENT) headers code.
     *
     * @see Response::HTTP_NO_CONTENT
     *
     * In the case that the resource isn't found, the response is
     * send using a 404 (NOT_FOUND) headers code
     * @see Response::HTTP_NOT_FOUND
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function deleteCommentary() : JsonResponse
    {
        $id = $this->request->getCurrentRequest()->get('id');

        $commentary = $this->doctrine->getRepository(Commentary::class)
                                     ->findOneBy([
                                         'id' => $id,
                                     ]);

        if ($commentary) {
            $this->doctrine->remove($commentary);
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
                'message' => 'Resource not found',
            ],
            Response::HTTP_NOT_FOUND
        );
    }
}
