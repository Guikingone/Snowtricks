<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Services\Api;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;
use UserBundle\Events\UserRegisteredEvent;
use UserBundle\Form\Type\LoginType;
use UserBundle\Form\Type\RegisterType;

class Security
{
    /** @var EntityManager */
    private $doctrine;

    /** @var FormFactory */
    private $form;

    /** @var UserPasswordEncoder */
    private $security;

    /** @var AuthenticationUtils */
    private $authentication;

    /** @var Workflow */
    private $workflow;

    /** @var DefaultEncoder */
    private $encoder;

    /** @var TraceableEventDispatcher */
    private $dispatcher;

    /** @var RequestStack */
    private $request;

    /**
     * Security constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param UserPasswordEncoder      $security
     * @param Workflow                 $workflow
     * @param DefaultEncoder           $encoder
     * @param TraceableEventDispatcher $dispatcher
     * @param RequestStack             $request
     */
    public function __construct (
        EntityManager $doctrine,
        FormFactory $form,
        UserPasswordEncoder $security,
        Workflow $workflow,
        DefaultEncoder $encoder,
        TraceableEventDispatcher $dispatcher,
        RequestStack $request
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->security = $security;
        $this->workflow = $workflow;
        $this->encoder = $encoder;
        $this->dispatcher = $dispatcher;
        $this->request = $request;
    }

    /**
     * Allow to register a new user using the data passed
     * through the request.
     *
     * In the case that the data are valid and the user can be created,
     * the response send a 201 (CREATED) headers code.
     *
     * @see Response::HTTP_CREATED
     *
     * In the case that the form is invalid after submission of the
     * data, the response send a 400 (BAD_REQUEST) headers code.
     *
     * @see Response::HTTP_BAD_REQUEST
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function register()
    {
        $user = new User();

        // Init the workflow
        $this->workflow->apply($user, 'register_phase');

        // Grab the data passed through the request.
        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(RegisterType::class, $user, [
            'csrf_protection' => false
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Send the event who's gonna set the password
            // and send the token to the user.
            $event = new UserRegisteredEvent($user);
            $this->dispatcher->dispatch(UserRegisteredEvent::NAME, $event);

            $this->doctrine->persist($user);
            $this->doctrine->flush();

            return new JsonResponse(
                [
                    $user,
                    'message' => 'Resource created'
                ],
                Response::HTTP_CREATED
            );
        }

        return new JsonResponse(
            [
                'message' => 'Form Invalid.'
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    public function login()
    {
        // TODO
    }
}