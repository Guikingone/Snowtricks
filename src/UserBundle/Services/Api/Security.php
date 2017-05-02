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
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Workflow\Workflow;

// Entity
use UserBundle\Entity\User;

// Events
use UserBundle\Events\ConfirmedUserEvent;
use UserBundle\Events\ForgotPasswordEvent;
use UserBundle\Events\UserRegisteredEvent;

// Forms
use UserBundle\Form\Type\ForgotPasswordType;
use UserBundle\Form\Type\LoginType;
use UserBundle\Form\Type\RegisterType;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Workflow\Exception\LogicException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class Security.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class Security
{
    /** @var EntityManager */
    private $doctrine;

    /** @var FormFactory */
    private $form;

    /** @var TokenStorage */
    private $tokenStorage;

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
     * @param TokenStorage             $tokenStorage
     * @param Workflow                 $workflow
     * @param DefaultEncoder           $encoder
     * @param TraceableEventDispatcher $dispatcher
     * @param RequestStack             $request
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        TokenStorage $tokenStorage,
        Workflow $workflow,
        DefaultEncoder $encoder,
        TraceableEventDispatcher $dispatcher,
        RequestStack $request
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->tokenStorage = $tokenStorage;
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
            'csrf_protection' => false,
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
                    'message' => 'Resource created',
                ],
                Response::HTTP_CREATED
            );
        }

        return new JsonResponse(
            [
                'message' => 'Form Invalid.',
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Allow to log a user by using the data passed through the request.
     *
     * In the case that the values are valid, the token is generated and send
     * through a 200 (OK) headers code.
     *
     * @see Response::HTTP_OK
     *
     * In the case that the values aren't valid, the response is send with
     * a 400 (BAD_REQUEST) headers code.
     *
     * @see Response::HTTP_BAD_REQUEST
     *
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws UsernameNotFoundException
     * @throws \InvalidArgumentException
     * @throws JWTEncodeFailureException
     *
     * @return JsonResponse
     */
    public function login()
    {
        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(LoginType::class);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->doctrine->getRepository('UserBundle:User')
                                   ->findOneBy([
                                       'username' => $data['_username']
                                   ]);

            if (!$user) {
                throw new UsernameNotFoundException(
                    sprintf(
                        'The username provide is invalid !'
                    )
                );
            }

            $token = new UsernamePasswordToken(
                $user, $data['_password'], 'api'
            );
            $this->tokenStorage->setToken($token);

            // Dispatch the event responsible for the login phase.
            $event = new InteractiveLoginEvent(
                $this->request->getCurrentRequest(),
                $token
            );
            $this->dispatcher->dispatch(InteractiveLoginEvent::class, $event);

            // Generate the token linked to this profile.
            $token = $this->encoder->encode([
                'username' => $data['_username'],
            ]);

            return new JsonResponse(
                [
                    'message' => 'Connexion successful',
                    'token' => $token,
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'message' => 'Form invalid.',
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Allow the user authenticated to retrieve his password using
     * his email and username.
     *
     * In the case that the values send are valid, the response
     * send a 200 (OK) headers code and the credentials are send by email.
     *
     * @see Response::HTTP_OK
     *
     * In the case that the values send aren't valid, the response
     * send a 400 (BAD_REQUEST) headers code.
     *
     * @see Response::HTTP_BAD_REQUEST
     *
     * @throws InvalidOptionsException
     * @throws AlreadySubmittedException
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function forgotPassword()
    {
        $data = $this->request->getCurrentRequest()->request->all();

        $form = $this->form->create(ForgotPasswordType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->doctrine->getRepository('UserBundle:User')
                                   ->findOneBy([
                                       'username' => $data['username'],
                                   ]);

            if (!$user) {
                throw new \LogicException(
                    sprintf(
                        'The user does not exist into the BDD, 
                    be sure to have to have submit the right username !'
                    )
                );
            }

            $event = new ForgotPasswordEvent($user);
            $this->dispatcher->dispatch(ForgotPasswordEvent::NAME, $event);

            return new JsonResponse(
                [
                    'message' => 'Credentials send by email.',
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'message' => 'Form invalid.',
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Allow to validate a user using the generated token.
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function validateUser()
    {
        $token = $this->request->getCurrentRequest()->get('token');

        if (!preg_match('/token_[a-z0-9A-Z]/', $token)) {
            return new JsonResponse(
                [
                    'message' => 'Invalid token',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($token) {
            $user = $this->doctrine->getRepository('UserBundle:User')
                                   ->findOneBy([
                                       'token' => $token,
                                   ]);

            if (!$user || $user->getValidated()) {
                return new JsonResponse(
                    [
                        'message' => 'Bad credentials || Resource already validated',
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if ($user->getToken() === $token) {
                $event = new ConfirmedUserEvent($user);
                $this->dispatcher->dispatch(ConfirmedUserEvent::NAME, $event);
            }
        }

        return new JsonResponse(
            [
                'message' => 'Resource validated.',
            ],
            Response::HTTP_OK
        );
    }

    public function lockUser()
    {

    }
}
