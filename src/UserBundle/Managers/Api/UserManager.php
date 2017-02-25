<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Managers\Api;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

// Entity
use Symfony\Component\Workflow\Workflow;
use UserBundle\Entity\User;

// Form
use UserBundle\Form\Type\RegisterType;

// Event
use UserBundle\Events\UserRegisteredEvent;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class ApiUserManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserManager
{
    /** @var EntityManager */
    private $doctrine;

    /** @var FormFactory */
    private $form;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var Workflow */
    private $workflow;

    /** @var RequestStack */
    private $request;

    /**
     * UserManager constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param EventDispatcherInterface $dispatcher
     * @param Workflow                 $workflow
     * @param RequestStack             $request
     */
    public function __construct (
        EntityManager $doctrine,
        FormFactory $form,
        EventDispatcherInterface $dispatcher,
        Workflow $workflow,
        RequestStack $request
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->dispatcher = $dispatcher;
        $this->workflow = $workflow;
        $this->request = $request;
    }

    /**
     * Allow to return all the users.
     *
     * @return array|User[]
     */
    public function getUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')
                              ->findAll();
    }

    /**
     * Return a single user using his firstname.
     *
     * @param string $name
     *
     * @return null|User
     */
    public function getUser(string $name)
    {
        return $this->doctrine->getRepository('UserBundle:User')
                              ->findOneBy([
                                  'lastname' => $name
                              ]);
    }

    /**
     * Return every users not validated.
     *
     * @return array|User[]
     */
    public function getUsersNotValidated()
    {
        return $this->doctrine->getRepository('UserBundle:User')
                              ->findBy([
                                  'validated' => false
                              ]);
    }

    /**
     * Return every users validated.
     *
     * @return array|User[]
     */
    public function getUsersValidated()
    {
        return $this->doctrine->getRepository('UserBundle:User')
                              ->findBy([
                                  'validated' => true
                              ]);
    }

    /**
     * Return all the users locked.
     *
     * @return array|User[]
     */
    public function getLockedUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')
                              ->findBy([
                                  'locked' => true
                              ]);
    }

    /**
     * Return all the users unlocked.
     *
     * @return array|User[]
     */
    public function getUnlockedUsers()
    {
        return $this->doctrine->getRepository('UserBundle:User')
                              ->findBy([
                                  'locked' => false
                              ]);
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

}
