<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Managers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Tricks;

// FormFactory
use Symfony\Component\Form\FormFactory;
use AppBundle\Form\Type\TricksType;
use AppBundle\Form\Type\UpdateTricksType;
use Symfony\Component\Form\FormView;

// Events
use AppBundle\Events\TricksValidatedEvent;
use AppBundle\Events\TricksRefusedEvent;
use AppBundle\Events\TricksDeletedEvent;

// Exceptions
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class TricksManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksManager
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var FormFactory
     */
    private $form;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TraceableEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * TricksManager constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param Workflow                 $workflow
     * @param Session                  $session
     * @param TraceableEventDispatcher $eventDispatcher
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        Session $session,
        TraceableEventDispatcher $eventDispatcher,
        Workflow $workflow
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->session = $session;
        $this->eventDispatcher = $eventDispatcher;
        $this->workflow = $workflow;
    }

    /**
     * Return all the tricks.
     *
     * @return Tricks[]|array
     */
    public function getAllTricks()
    {
        return $this->doctrine->getRepository('AppBundle:Tricks')->findAll();
    }

    /**
     * Return a simple tricks using his name.
     *
     * @param string $name
     *
     * @return \AppBundle\Entity\Tricks|null
     */
    public function getTricksByName(string $name)
    {
        return $this->doctrine->getRepository('AppBundle:Tricks')->findOneBy(['name' => $name]);
    }

    /**
     * Allow to add a single trick.
     *
     * @param Request $request
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse|\Symfony\Component\Form\FormView
     */
    public function addTrick(Request $request)
    {
        $trick = new Tricks();

        // Init the workflow phase.
        $this->workflow->apply($trick, 'start_phase');

        $form = $this->form->create(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->persist($trick);
            $this->doctrine->flush();

            $redirect = new RedirectResponse('home');
            $redirect->send();
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param string  $name
     *
     * @throws \LogicException
     * @throws InvalidOptionsException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return FormView|RedirectResponse
     */
    public function updateTricks(Request $request, string $name)
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy([
                                     'name' => $name,
                                 ]);

        if (is_object($tricks) && !$tricks instanceof Tricks) {
            throw new \LogicException(
                sprintf(
                    'The entity MUST be a instance of Tricks !, 
                    given "%s"', get_class($tricks)
                )
            );
        }

        // Lock the publication of the trick.
        $tricks->setPublished(false);

        $form = $this->form->create(UpdateTricksType::class, $tricks);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->flush();
            $this->session->getFlashBag()->add(
                'success',
                'Le trick a bien été modifié.'
            );

            $redirect = new RedirectResponse('tricks');
            $redirect->send();
        }

        return $form->createView();
    }

    /**
     * Allow to validate a tricks using his name.
     *
     * @param string $name
     *
     * @throws \LogicException
     * @throws LogicException
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function validateTricks(string $name)
    {
        try {
            if (is_object($name)) {
                throw new \LogicException(
                    sprintf(
                        'The argument MUST be a string, 
                    given "%s"', gettype($name)
                    )
                );
            }
        } catch (\LogicException $exception) {
            $exception->getMessage();
        }

        if (is_string($name)) {
            $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                    ->findOneBy([
                                        'name' => $name,
                                    ]);

            if ($trick instanceof Tricks
                && array_key_exists('validation', $trick->currentState)) {

                // Set the workflow phase.
                $this->workflow->apply($trick, 'validation_phase');

                // Validate the trick.
                $trick->setValidated(true);

                // Finalize the workflow.
                $this->workflow->apply($trick, 'publication_phase');

                // Dispatch a new Event.
                $event = new TricksValidatedEvent($trick);
                $this->eventDispatcher->dispatch(
                    TricksValidatedEvent::NAME,
                    $event
                );
            }
        }

        return new RedirectResponse('back');
    }

    /**
     * Allow to refuse a trick using his name.
     *
     * @param string $name
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function refuseTricks(string $name)
    {
        try {
            if (is_object($name)) {
                throw new \LogicException(
                    sprintf(
                        'The argument MUST be a string, 
                    given "%s"', gettype($name)
                    )
                );
            }
        } catch (\LogicException $exception) {
            $exception->getMessage();
        }

        if (is_string($name)) {
            $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                    ->findOneBy(['name' => $name]);

            if ($trick instanceof Tricks) {
                $trick->setValidated(false);
                // Dispatch a new Event.
                $event = new TricksRefusedEvent($trick);
                $this->eventDispatcher->dispatch(TricksRefusedEvent::NAME, $event);
            }
        }

        return new RedirectResponse('back');
    }

    /**
     * Allow to delete a tricks using his name.
     *
     * @param string $name
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function deleteTricks(string $name)
    {
        try {
            if (is_object($name)) {
                throw new \LogicException(
                    sprintf(
                        'The argument MUST be a string, 
                    given "%s"', gettype($name)
                    )
                );
            }
        } catch (\LogicException $exception) {
            $exception->getMessage();
        }

        if (is_string($name)) {
            $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                    ->findOneBy([
                                        'name' => $name,
                                    ]);

            if ($trick instanceof Tricks) {
                $this->doctrine->remove($trick);
                // Dispatch a new Event.
                $event = new TricksDeletedEvent($trick);
                $this->eventDispatcher->dispatch(TricksDeletedEvent::NAME, $event);
            }
        }

        return new RedirectResponse('back');
    }
}
