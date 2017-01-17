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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

// Entity
use AppBundle\Entity\Commentary;
use AppBundle\Entity\Tricks;

// Form
use Symfony\Component\Form\FormFactory;
use AppBundle\Form\Type\CommentaryType;
use Symfony\Component\Form\FormView;

// Exceptions
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;

/**
 * Class CommentaryManager.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryManager
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
     * CommentaryManager constructor.
     *
     * @param EntityManager $doctrine
     * @param FormFactory   $form
     * @param Session       $session
     */
    public function __construct(
        EntityManager $doctrine,
        FormFactory $form,
        Session $session
    ) {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->session = $session;
    }

    /**
     * Allow to find a single commentary by using the tricks name and commentary id.
     *
     * @param string $name
     * @param int    $id
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @return Commentary|null
     */
    public function getCommentaryByTricks(string $name, int $id)
    {
        return $this->doctrine->getRepository('AppBundle:Commentary')->getCommentaryByTricks($name, $id);
    }

    /**
     * Return all the commentaries linked to a tricks using the tricks name.
     *
     * @param string $tricks
     *
     * @return \AppBundle\Entity\Commentary[]|array
     */
    public function getCommentariesByTricks(string $tricks)
    {
        return $this->doctrine->getRepository('AppBundle:Commentary')->getCommentariesByTricks($tricks);
    }

    /**
     * @param Request $request
     *
     * @throws InvalidOptionsException
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     *
     * @return FormView
     */
    public function addCommentary(Request $request)
    {
        $commentary = new Commentary();

        $form = $this->form->create(CommentaryType::class, $commentary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->persist($commentary);
            $this->doctrine->flush();
        }

        return $form->createView();
    }

    /**
     * Allow to delete all the commentaries linked to a tricks
     * using the tricks name.
     *
     * @param string $name
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function deleteCommentaries(string $name)
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
                $commentaries = $trick->getCommentary();

                foreach ($commentaries as $commentary) {
                    $this->doctrine->remove($commentary);
                }
            }
        }

        return new RedirectResponse('back');
    }

    /**
     * Allow to delete a commentary linked to a tricks using the tricks name
     * and the commentary id.
     *
     * @param string $name
     * @param int    $id
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ORMInvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function deleteCommentary(string $name, int $id)
    {
        try {
            if (is_object($name) || !is_int($id)) {
                throw new \LogicException(
                    sprintf(
                        'The arguments MUST be a string and an integer, 
                    given "%s"', gettype([$name, $id])
                    )
                );
            }
        } catch (\LogicException $exception) {
            $exception->getMessage();
        }

        if (is_string($name) && is_int($id)) {
            $commentary = $this->doctrine->getRepository('AppBundle:Commentary')
                                         ->getCommentaryByTricks($name, $id);

            if ($commentary) {
                $this->doctrine->remove($commentary);
                $this->session->getFlashBag()->add(
                    'success',
                    'Le commentaire a bien été supprimé.'
                );

                return new RedirectResponse('tricks');
            }
        }

        return new RedirectResponse('tricks');
    }
}