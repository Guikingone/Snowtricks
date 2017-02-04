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
use Symfony\Component\Workflow\Workflow;

// Entity
use AppBundle\Entity\Tricks;

// FormFactory
use Symfony\Component\Form\FormFactory;
use AppBundle\Form\Type\TricksType;
use AppBundle\Form\Type\UpdateTricksType;
use Symfony\Component\Form\FormView;

// Events
use AppBundle\Events\Tricks\TricksAddedEvent;
use AppBundle\Events\Tricks\TricksUpdatedEvent;
use AppBundle\Events\Tricks\TricksValidatedEvent;
use AppBundle\Events\Tricks\TricksRefusedEvent;
use AppBundle\Events\Tricks\TricksDeletedEvent;

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
    /** @var EntityManager */
    private $doctrine;

    /** @var FormFactory */
    private $form;

    /** @var TraceableEventDispatcher */
    private $eventDispatcher;

    /** @var Workflow */
    private $workflow;

    /**
     * TricksManager constructor.
     *
     * @param EntityManager            $doctrine
     * @param FormFactory              $form
     * @param TraceableEventDispatcher $eventDispatcher
     * @param Workflow                 $workflow
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        TraceableEventDispatcher $eventDispatcher,
        Workflow $workflow
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
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
     * Return a single tricks using his id.
     *
     * @param int $id
     *
     * @return Tricks|null
     */
    public function getTricksById(int $id)
    {
        return $this->doctrine->getRepository('AppBundle:Tricks')->findOneBy(['id' => $id]);
    }

    /**
     * @param Request $request
     *
     * @throws LogicException
     * @throws InvalidOptionsException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     *
     * @return FormView
     */
    public function addTrick(Request $request)
    {
        $trick = new Tricks();

        // Init the workflow phase.
        $this->workflow->apply($trick, 'start_phase');

        $form = $this->form->create(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Send a new event to perform new instance actions.
            $event = new TricksAddedEvent($trick);
            $this->eventDispatcher->dispatch(TricksAddedEvent::NAME, $event);

            $this->doctrine->persist($trick);
            $this->doctrine->flush();

            if ($trick->getPublished()) {
                // Only if it's a Admin add.
                $redirect = new RedirectResponse('tricks'.$trick->getName());
                $redirect->send();
            }

            $redirect = new RedirectResponse('tricks');
            $redirect->send();
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param string  $name
     *
     * @throws \InvalidArgumentException
     * @throws InvalidOptionsException
     * @throws OptimisticLockException
     *
     * @return FormView
     */
    public function updateTricks(Request $request, string $name)
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy([
                                     'name' => $name,
                                 ]);
        if (!$tricks) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The tricks name isn\'t correct !'
                )
            );
        }

        // Lock the publication of the trick.
        $tricks->setPublished(false);

        $form = $this->form->create(UpdateTricksType::class, $tricks);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Send a new Event for update phase.
            $event = new TricksUpdatedEvent($tricks);
            $this->eventDispatcher->dispatch(TricksUpdatedEvent::NAME, $event);

            $this->doctrine->flush();

            $redirect = new RedirectResponse('tricks');
            $redirect->send();
        }

        return $form->createView();
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function validateTricks(string $name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The argument MUST be a string, 
                    given "%s"', gettype($name)
                )
            );
        }

        if (is_string($name)) {
            $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                    ->findOneBy([
                                        'name' => $name,
                                    ]);

            if ($trick instanceof Tricks
                && array_key_exists('validation', $trick->currentState)) {

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
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function refuseTricks(string $name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The argument MUST be a string, 
                    given "%s"', gettype($name)
                )
            );
        }

        if (is_string($name)) {
            $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                    ->findOneBy(['name' => $name]);

            if ($trick instanceof Tricks) {
                $trick->setValidated(false);
                // Dispatch a new Event.
                $event = new TricksRefusedEvent($trick);
                $this->eventDispatcher->dispatch(TricksRefusedEvent::NAME, $event);

                $this->doctrine->remove($trick);
                $this->doctrine->flush();
            }
        }

        return new RedirectResponse('back');
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return RedirectResponse
     */
    public function deleteTricks(string $name)
    {
        if (is_object($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The argument MUST be a string, 
                    given "%s"', gettype($name)
                )
            );
        }

        if (is_string($name)) {
            $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                    ->findOneBy([
                                        'name' => $name,
                                    ]);

            if ($trick instanceof Tricks) {
                // Dispatch a new Event.
                $event = new TricksDeletedEvent($trick);
                $this->eventDispatcher->dispatch(TricksDeletedEvent::NAME, $event);

                $this->doctrine->remove($trick);
                $this->doctrine->flush();
            }
        }

        return new RedirectResponse('back');
    }
}
